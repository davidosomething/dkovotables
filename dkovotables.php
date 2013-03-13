<?php
/**
 * Plugin Name: DKO Votables
 * Plugin URI:  http://github.com/davidosomething/dkovotables
 * Description: Voting framework for WordPress. Lets you create objects to vote on, quizzes, polls, posts, etc.
 * Author:      davidosomething
 * Version:     0.1.5
 * Author URI:  http://www.davidosomething.com/
 */

// singleton!
class DKOVotables
{

  const VERSION       = '0.1.5';
  const SLUG          = 'DKOVotables';
  const DIRNAME       = 'dkovotables';
  const BASEPATH      = __DIR__;
  const SCREEN_ID     = 'toplevel_page_DKOVotables';

  // Singleton instance
  private static $instance = null;

  // Options
  public $default_options = array(
    'version' => '0.0.0',
  );

  // Admin vars
  private $screen_id; // check user on correct screen with this
  private $main_page; // submit forms here

  // Database vars
  public $votes_table_name;
  public $groups_table_name;
  public $votables_table_name;

  public static $votes_cache;

  // JavaScript status
  public $must_enqueue_js = false;


  /**
   * __construct
   *
   * @return void
   */
  public function __construct() {
    global $wpdb;
    self::$instance = $this;

    // initialize vars
    $this->screen_id = 'toplevel_page_' . self::SLUG;
    $this->main_page = admin_url('admin.php?page=' . self::SLUG);
    $this->votes_table_name = $wpdb->prefix . 'dkovotable_votes';
    $this->groups_table_name = $wpdb->prefix . 'dkovotable_groups';
    $this->votables_table_name = $wpdb->prefix . 'dkovotable_votes_to_groups';
    self::$votes_cache = array();

    $this->_setup_options();

    // Make sure db is created/up-to-date
    register_activation_hook(__FILE__, array($this, 'ensure_version'));
    add_action('plugins_loaded', array($this, 'ensure_version'));


    add_action('init', array($this, 'register_scripts'));

    // Add admin page and help
    add_action('admin_menu', array($this, 'add_root_menu'));
    add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    add_action('wp_ajax_dkovotable_vote', array($this, 'vote'));
    add_filter('contextual_help', array($this, 'plugin_help'), 10, 3);

    // Frontend
    add_shortcode('dkovotable', array($this, 'shortcode'));
    add_action('wp_footer', array($this, 'print_scripts'));
  }


////////////////////////////////////////////////////////////////////////////////
// Boilerplate /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

  /**
   * get_instance
   * Return the static instance of DKOVotables
   *
   * @return object
   */
  public static function get_instance() {
    if (is_null(self::$instance)) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   * _setup_options
   * Get and merge stored options with defaults.
   *
   * @return void
   */
  private function _setup_options() {
    $options = get_option('dkovotables_options');
    if (empty($options)) {
      $options = array();
    }
    $this->options = wp_parse_args($options, $this->default_options);
  }

  /**
   * ensure_version
   * Compare the activated version to this file. Update database if not same.
   *
   * @return void
   */
  public function ensure_version() {
    $installed_version = $this->options['version'];
    if ($installed_version !== self::VERSION) {
      $this->_update_database();
    }
  }

////////////////////////////////////////////////////////////////////////////////
// Database ////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

  /**
   * _update_database
   * Create/update database tables associated to this plugin.
   *
   * @return void
   */
  private function _update_database() {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Create/update the votes table
    $sql = "CREATE TABLE {$this->votes_table_name} (
      id          mediumint(9)                                NOT NULL AUTO_INCREMENT,
      description varchar(255)  DEFAULT ''                    NOT NULL,
      votes       mediumint(9)  DEFAULT 0                     NOT NULL,
      create_date datetime      DEFAULT '0000-00-00 00:00:00' NOT NULL,
      UNIQUE KEY id (id)
    );";
    dbDelta($sql);

    // Create/update the groups table
    $sql = "CREATE TABLE " . $this->groups_table_name . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name varchar(255) DEFAULT '' NOT NULL,
      description text NOT NULL,
      create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      UNIQUE KEY id (id)
    );";
    dbDelta($sql);

    // Create/update the votables-groups relationship table
    $sql = "CREATE TABLE {$this->votables_table_name} (
      id          mediumint(9)                                NOT NULL AUTO_INCREMENT,
      votes_id    mediumint(9)  DEFAULT 0                     NOT NULL,
      group_id    mediumint(9)  DEFAULT 0                     NOT NULL,
      create_date datetime      DEFAULT '0000-00-00 00:00:00' NOT NULL,
      UNIQUE KEY id (id)
    );";
    dbDelta($sql);

    $this->options['version'] = self::VERSION;
    update_option('dkovotables_options', $this->options);
  }

  /**
   * cache_votes
   * Get votes count from SQL results of array of objects
   * Store in $this->votes_cache array, indexed using votablee id
   *
   * @param mixed $votes
   * @return void
   */
  protected function cache_votes($votes) {
    foreach ($votes as $vote) {
      self::$votes_cache[$vote->id] = $vote->votes;
    }
  }

  /**
   * get_count
   * get the number of votes for a votable
   *
   * @param int $id
   * @return int
   */
  public function get_count($id) {
    return self::$votes_cache[$id];
  }

  /**
   * get_groups
   *
   * @return array of group objects
   */
  public function get_groups() {
    global $wpdb;
    static $groups_cache;
    if (empty($groups_cache)) {
      $sql = "SELECT id, name, description FROM {$this->groups_table_name}";
      $groups_cache = $wpdb->get_results($sql);
    }
    return $groups_cache;
  }

  /**
   * get_group
   * Return the ALL group or some cached object from the groups_cache
   *
   * @param mixed $id
   * @return null|object
   */
  public function get_group($id) {
    if (empty($id)) {
      return (object)array(
        'id'          => 0,
        'name'        => 'ALL',
        'description' => 'Votables from ALL groups'
      );
    }

    $group_ids = wp_list_pluck($this->get_groups(), 'id');
    $group_id = array_search($id, $group_ids, false);

    // return null if group not found
    if ($group_id === false) {
      return null;
    }

    // return the found group
    return $this->get_groups()[$group_id];
  }

////////////////////////////////////////////////////////////////////////////////
// Admin ///////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

  /**
   * add_root_menu
   *
   * @return void
   */
  public function add_root_menu() {
    add_menu_page(
      'Votables', // title tags
      'Votables', // on screen
      'manage_options',
      self::SLUG,
      array($this, 'admin_page'),
      'http://s.gravatar.com/avatar/dcf949116994998753bd171a74f20fe9?s=16',
      100.001
    );
    add_action('admin_print_styles-' . $this->screen_id, array($this, 'admin_enqueue_styles'));
  }

  /**
   * admin_page
   * Controller for the admin page
   *
   * @return void
   */
  public function admin_page() {
    include 'controller/admin.php';
  }

  /**
   * admin_enqueue_scripts
   * Enqueue the admin side JS
   *
   * @access public
   * @return void
   */
  public function admin_enqueue_scripts($screen_id) {
    if ($screen_id !== self::SCREEN_ID) {
      return;
    }
    // enqueue the main JS
    wp_enqueue_script( self::SLUG );

    // enqueue the admin JS which relies on the main JS
    wp_enqueue_script(
      self::SLUG . '-admin',
      plugins_url('/assets/js/admin.js', __FILE__),
      array('jquery', self::SLUG),
      self::VERSION,
      true
    );
  }

  /**
   * admin_enqueue_styles
   *
   * @access public
   * @return void
   */
  public function admin_enqueue_styles() {
    wp_enqueue_style(
      self::SLUG . '-admin',
      plugins_url('/assets/css/admin.css', __FILE__)
    );
  }

  /**
   * plugin_help
   * Sets $contextual_help value if on the Votables admin screen.
   *
   * @param string $contextual_help
   * @param string $screen_id
   * @param mixed $screen
   * @return void
   */
  public function plugin_help($contextual_help, $screen_id, $screen) {
    if ($screen_id !== $this->screen_id) {
      return;
    }
    ob_start();
    include plugin_dir_path(__FILE__) . 'view/contextual_help.php';
    $contextual_help = ob_get_contents();
    ob_end_clean();
    return $contextual_help;
  }


////////////////////////////////////////////////////////////////////////////////
// Frontend ////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

  /**
   * register_scripts
   *
   * @return void
   */
  public function register_scripts() {
    wp_register_script(
      self::SLUG,
      plugins_url('assets/js/script.js', __FILE__),
      array('jquery'),
      self::VERSION,
      true
    );
  }

  /**
   * shortcode
   * Also sets the must_enqueue_js var, which determines if the frontend JS
   * should be loaded.
   *
   * @return void
   */
  public function shortcode($atts, $content = null) {
    $this->must_enqueue_js = true;

    extract(shortcode_atts(array(
      'id'      => 0,
      'action'  => 'link'
    ), $atts));

    $output = '';

    // Shortcode action attribute requests a link to vote for the votable
    if ($action === 'link') {
      $id = (int)$id;
      if (!$id) {
        return '[missing votable id]';
      }

      if (!$content) {
        $content = 'Vote for votable #' . $id;
      }

      $output = '<div class="dkovotable" data-action="vote" data-id="' . $id . '">';
      $output .= $content;
      $output .= '</div>';
      return $output;
    }

    // Shortcode action attribute requests a link to vote for the votable
    elseif ($action === 'count') {
      $id = (int)$id;
      if (!$id) {
        return '[missing votable id]';
      }

      $output = '<span class="dkovotable dkovotable-textcounter" data-id="' . $id . '" data-action="count" data-count="' . $this->get_count($id) . '">';
      $output .= $this->get_count($id);
      $output .= '</span>';
      return $output;
    }

    return $output;
  }

  /**
   * print_scripts
   * Hook for wp_footer, prints scripts if the must_enqueue_js var is true,
   * which is only when the shortcode has been used.
   *
   * @return void
   */
  public function print_scripts() {
    if (!$this->must_enqueue_js) {
      return;
    }
    wp_print_scripts('dkovotables');
  }


////////////////////////////////////////////////////////////////////////////////
// AJAX Hooks //////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
  /**
   * output_json
   * Outputs JSON
   *
   * @param array $data
   * @return void
   */
  protected function output_json($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    die(); // this is required to return a proper result
  }

  /**
   * vote
   * Hooked into wp_ajax_dkovotable_vote
   * So AJAX calls to ajaxurl with action "dkovotable_vote" will use this function
   *
   * @access public
   * @return void
   */
  public function vote() {
    global $wpdb; // this is how you get access to the database

    // sanitize POST data
    $id = (int)$_POST['id'];
    if (!$id) {
      $this->output_json(array('error' => 'Missing ID parameter.'));
      die();
    }

    // get current votes
    $sql = $wpdb->prepare("SELECT votes FROM {$this->votes_table_name} WHERE id = %d", $id);
    $current_votes = $wpdb->get_var($sql);
    if ($current_votes === false) {
      $this->output_json(array('error' => 'Votable ID not found.'));
      die();
    }

    $new_votes = $current_votes + 1;

    // increment votes
    $update = $wpdb->update(
      $this->votes_table_name,
      array('votes' => $new_votes),
      array('id' => $id),
      array('%d'),
      array('%d')
    );

    if ($update === false) {
      $this->output_json(array('error' => 'Couldn\'t increment votes for votable with id ' . $id));
      die();
    }

    if ($update === 0) {
      $this->output_json(array('error' => 'Nothing was updated trying to increment votes for votable with id ' . $id));
      die();
    }

    $this->output_json(array(
      'success' => true,
      'id'      => $id,
      'votes'   => $new_votes
    ));
    die(); // this is required to return a proper result
  }

}
$dkovotables = DKOVotables::get_instance();
