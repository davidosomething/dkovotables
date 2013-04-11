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
  private $admin_messages = array();

  // Database vars
  public $votes_table_name;
  public $groups_table_name;
  public $votables_table_name;

  public static $votes_cache = array();
  public static $group_votes_cache = array();

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

    // Backend
    add_action('init', array($this, 'handle_forms'));

    // Add admin page and help
    add_action('admin_menu', array($this, 'add_root_menu'));
    add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    add_action('admin_notices', array($this, 'admin_notices'));
    add_filter('contextual_help', array($this, 'plugin_help'), 10, 3);

    // AJAX hooks
    add_action('wp_ajax_dkovotable_vote', array($this, 'vote'));
    add_action('wp_ajax_nopriv_dkovotable_vote', array($this, 'vote'));
    add_action('wp_ajax_dkovotable_count', array($this, 'count'));
    add_action('wp_ajax_nopriv_dkovotable_count', array($this, 'count'));

    // Frontend
    add_shortcode('dkovotable', array($this, 'shortcode'));
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
   * Store in $this->votes_cache array, indexed using votable id
   *
   * @param object $votes
   * @return void
   */
  protected function cache_votes($votes) {
    foreach ($votes as $vote) {
      self::$votes_cache[$vote->id] = $vote->votes;
    }
  }

  /**
   * cache_group_votes
   * Get a group's votes count from SQL results of array of objects
   * Store in $this->group_votes_cache array, indexed using group_name
   *
   * @param object $votes
   * @return void
   */
  protected function cache_group_votes($groups) {
    foreach ($groups as $group) {
      self::$group_votes_cache[$group->name] = $group->votes;
    }
  }

  /**
   * get_count
   * get the number of votes for a thing
   *
   * @return int
   */
  public function get_count() {
    if (!empty($_POST['votable_id'])) {
      return $this->get_votable_count($_POST['votable_id']);
    }
    elseif (!empty($_POST['group_name'])) {
      return $this->get_group_count($_POST['group_name']);
    }
    return 0;
  }

  /**
   * get_votable_count
   * get the number of votes for a votable
   *
   * @param int $votable_id
   * @return int
   */
  public function get_votable_count($votable_id) {
    global $wpdb;
    if (isset(self::$votes_cache[$votable_id])) {
      return self::$votes_cache[$votable_id];
    }
    else {
      $query = $wpdb->prepare("
        SELECT
          votables.id AS id,
          votes.votes AS votes
        FROM {$this->votables_table_name} AS votables
        LEFT OUTER JOIN {$this->votes_table_name} AS votes ON votes.id = votables.votes_id
        WHERE votables.id = %d
        LIMIT 1
        ",
        $votable_id
      );
      $result = $wpdb->get_row($query);
      // @TODO log instead of output
      if (!$result) {
        echo '<p class="error">Error getting votes for ', htmlspecialchars($votable_id), '</p>';
      }
      $this->cache_votes(array($result));
      return $result->votes;
    }
    return 0;
  }

  /**
   * get_group_count
   * get the number of votes for an entire group
   *
   * @param int $group_id
   * @return int
   */
  public function get_group_count($group_name) {
    global $wpdb;
    if (isset(self::$group_votes_cache[$group_name])) {
      return self::$group_votes_cache[$group_name];
    }
    else {
      $query = $wpdb->prepare("
        SELECT
          groups.id AS id,
          groups.name AS name,
          SUM(votes.votes) AS votes
        FROM {$this->groups_table_name} AS groups 
        INNER JOIN {$this->votables_table_name} AS votables ON votables.group_id = groups.id
        LEFT OUTER JOIN {$this->votes_table_name} AS votes ON votes.id = votables.votes_id
        WHERE groups.name = %s
        LIMIT 1
        ",
        $group_name
      );
      $result = $wpdb->get_row($query);
      // @TODO log instead of output
      if (!$result) {
        echo '<p class="error">Error getting votes for group ', htmlspecialchars($group_name), '</p>';
      }
      $this->cache_group_votes(array($result));
      return $result->votes;
    }
    return 0;
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
  public function get_group($by = 'id', $term = 0) {
    if (!in_array($by, array('id', 'name'))) {
      return null;
    }

    if (!$term) {
      return (object)array(
        'id'          => 0,
        'name'        => 'ALL',
        'description' => 'Votables from ALL groups'
      );
    }

    $plucked_groups = wp_list_pluck($this->get_groups(), $by);
    $group_index = array_search($term, $plucked_groups, false);

    // return null if group not found
    if ($group_index === false) {
      return null;
    }

    // return the found group
    $groups = $this->get_groups();
    if (is_array($groups)) return $groups[$group_index];

    return null;
  }

////////////////////////////////////////////////////////////////////////////////
// Backend /////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

  /**
   * handle_forms
   *
   * @return void
   */
  public function handle_forms() {
    if (!empty($_POST[DKOVotables::SLUG])) {
      if (wp_verify_nonce($_POST[DKOVotables::SLUG], 'create_votable')) {
        $this->create_votable();
      }
      elseif (wp_verify_nonce($_POST[DKOVotables::SLUG], 'create_group')) {
        $this->create_group();
      }
      else {
        $this->admin_messages[] = array(
          'is_error'  => true,
          'content'   => '<p>Invalid nonce.</p>'
        );
      }
      return; // you can only CREATE OR DELETE, not both at once
    }

    if (!empty($_GET['_wpnonce'])) {
      $nonce = $_GET['_wpnonce'];
      $votable_id = empty($_GET['votable_id']) ? 0 : (int)$_GET['votable_id'];
      if (wp_verify_nonce($nonce, 'delete_votable_' . $votable_id)) {
        $this->delete_votable($votable_id);
        return;
      }
      elseif (wp_verify_nonce($nonce, 'reset_votable_' . $votable_id)) {
        $this->reset_votable($votable_id);
        return;
      }
      else {
        $this->admin_messages[] = array(
          'is_error'  => true,
          'content'   => '<p>Invalid nonce.</p>'
        );
      }
    }

  }

  /**
   * create_votable
   * Using POST data, create a new vote record and associate it to a group as
   * a votable
   *
   * @return void
   */
  protected function create_votable() {
    global $wpdb;

    // REQUIRED FIELDS
    if (empty($_POST['create_votable_description'])) {
      $this->admin_messages[] = array(
        'is_error'  => true,
        'content'   => '<p>Please provide a description for the votable you are trying to create.</p>'
      );
      return;
    }

    // SANITIZE
    $votes = empty($_POST['create_votable_votes']) ? 0 : (int)$_POST['create_votable_votes'];
    $group_id = empty($_POST['create_votable_group']) ? 0 : (int)$_POST['create_votable_group'];

    // do not mysql escape
    $create_votes = $wpdb->insert(
      $this->votes_table_name,
      array(
        'description' => $_POST['create_votable_description'],
        'votes'       => $votes,
        'create_date' => current_time('mysql')
      ),
      array(
        '%s',
        '%d'
      )
    );

    if ($create_votes === false) {
      $this->admin_messages[] = array(
        'is_error'  => true,
        'content'   => '<p>There was an error create a vote record for the votable.</p>'
      );
      return;
    }

    $votes_id = $wpdb->insert_id;

    $create_votable = $wpdb->insert(
      $this->votables_table_name,
      array(
        'votes_id' => $votes_id,
        'group_id' => $group_id,
        'create_date' => current_time('mysql')
      ),
      array(
        '%d',
        '%d',
        '%s'
      )
    );

    if ($create_votable === false) {
      $this->admin_messages[] = array(
        'is_error'  => true,
        'content'   => '<p>There was an error creating the votable.</p>'
      );
      return;
    }

    $group = $this->get_group('id', $group_id);
    $this->admin_messages[] = array(
      'is_error'  => false,
      'content'   => '<p>Successfully created a new votable in group <em>' . $group->name . '</em>.</p>'
    );
    return;
  }

  /**
   * create_group
   * Using POST data, create a new group
   *
   * @return void
   */
  protected function create_group() {
    global $wpdb;

    // REQUIRED FIELDS
    if (empty($_POST['create_group_name'])) {
      $this->admin_messages[] = array(
        'is_error'  => true,
        'content'   => '<p>Please provide a name for the group you are trying to create.</p>'
      );
      return;
    }

    if (empty($_POST['create_group_description'])) {
      $this->admin_messages[] = array(
        'is_error'  => true,
        'content'   => '<p>Please provide a description for the group you are trying to create.</p>'
      );
      return;
    }

    // do not mysql escape
    $create_group = $wpdb->insert(
      $this->groups_table_name,
      array(
        'name'        => $_POST['create_group_name'],
        'description' => $_POST['create_group_description'],
        'create_date' => current_time('mysql')
      ),
      array(
        '%s',
        '%s',
        '%s'
      )
    );

    if ($create_group === false) {
      $this->admin_messages[] = array(
        'is_error'  => true,
        'content'   => '<p>There was an error creating the group.</p>'
      );
      return;
    }

    $group_name = htmlspecialchars($_POST['create_group_name']);
    $this->admin_messages[] = array(
      'is_error'  => false,
      'content'   => '<p>Successfully created the group <em>' . $group_name . '</em>.</p>'
    );
    return;
  }

  /**
   * delete_votable
   *
   * @return void
   */
  protected function delete_votable($votable_id) {
    global $wpdb;
    if (!is_int($votable_id)) {
      $this->admin_messages[] = array(
        'is_error'  => true,
        'content'   => '<p>Invalid ID provided when trying to delete votable.</p>'
      );
      return;
    }

    $query = $wpdb->prepare("
      SELECT votes_id FROM {$this->votables_table_name}
      WHERE id = %d
      ", $votable_id
    );
    $vote_id = $wpdb->get_var($query);

    // delete the vote for this votable
    if ($vote_id) {
      $query = $wpdb->prepare("
        DELETE FROM {$this->votes_table_name}
        WHERE id = %d
        ",
        $vote_id
      );
      $result = $wpdb->query($query);
    }

    // delete the associated votable, leaving only the group
    $query = $wpdb->prepare("
      DELETE FROM {$this->votables_table_name}
      WHERE id = %d
      ", $votable_id
    );
    $result = $wpdb->query($query);

    if ($result === FALSE) {
      $this->admin_messages[] = array(
        'is_error'  => true,
        'content'   => '<p>MySQL error when trying to delete votable.</p>'
      );
      return;
    }

    if (!$result) {
      $this->admin_messages[] = array(
        'is_error'  => true,
        'content'   => '<p>Tried to delete votable but nothing was deleted.</p>'
      );
      return;
    }

    $this->admin_messages[] = array(
      'is_error'  => false,
      'content'   => '<p>Deleted votable #' . $votable_id . '.</p>'
    );
    return;
  }

  /**
   * reset_votable
   *
   * @return void
   */
  protected function reset_votable($votable_id) {
    global $wpdb;
    if (!is_int($votable_id)) {
      $this->admin_messages[] = array(
        'is_error'  => true,
        'content'   => '<p>Invalid ID provided when trying to reset votable.</p>'
      );
      return;
    }

    $query = $wpdb->prepare("
      SELECT votes_id FROM {$this->votables_table_name}
      WHERE id = %d
      ", $votable_id
    );
    $vote_id = $wpdb->get_var($query);

    // delete the vote for this votable
    if (!$vote_id) {
      $this->admin_messages[] = array(
        'is_error'  => true,
        'content'   => '<p>MySQL error when trying to select vote id to reset.</p>'
      );
      return;
    }

    $query = $wpdb->prepare("
      UPDATE {$this->votes_table_name}
      SET votes = 0
      WHERE id = %d
      ",
      $vote_id
    );
    $result = $wpdb->query($query);

    if ($result === FALSE) {
      $this->admin_messages[] = array(
        'is_error'  => true,
        'content'   => '<p>MySQL error when trying to reset votes.</p>'
      );
      return;
    }

    if (!$result) {
      $this->admin_messages[] = array(
        'is_error'  => true,
        'content'   => '<p>Tried to reset votes but nothing was reset.</p>'
      );
      return;
    }

    $this->admin_messages[] = array(
      'is_error'  => false,
      'content'   => '<p>Reset votable #' . $votable_id . '.</p>'
    );
    return;
  }


////////////////////////////////////////////////////////////////////////////////
// Common to Admin and Visitor /////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

  /**
   * enqueue_script
   * Enqueues the main dkovotables script
   *
   * @return void
   */
  public function enqueue_script() {
    wp_enqueue_script(
      self::SLUG,
      plugins_url('assets/js/script.js', __FILE__),
      array('jquery'),
      self::VERSION
    );
    wp_localize_script(
      self::SLUG,
      self::SLUG,
      array(
        'ajaxurl' => admin_url('admin-ajax.php')
      )
    );
  }


////////////////////////////////////////////////////////////////////////////////
// Admin ///////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

  /**
   * Display messages in the CMS
   */
  public function admin_notices() {
    if (!count($this->admin_messages)) {
      return;
    }
    foreach ($this->admin_messages as $message) {
      if (array_key_exists('is_error', $message) && $message['is_error']) {
        echo '<div class="error">';
      }
      else {
        echo '<div class="updated">';
      }
      echo $message['content'];
      echo '</div>';
    }
  }

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
      '500.00001'
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
    $this->enqueue_script();

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

  /**
   * admin_link
   * Create a link to perform some admin action
   *
   * @param int $votable_id
   * @return string
   */
  protected function admin_link($action, $votable_id) {
    $query = build_query(array(
      'votable_id' => $votable_id
    ));
    $url = $this->main_page . '&amp;' . $query;
    $nonce_action = $action . '_votable_' . $votable_id;
    return wp_nonce_url($url, $nonce_action);
  }


////////////////////////////////////////////////////////////////////////////////
// Frontend ////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

  /**
   * shortcode
   * Also sets the must_enqueue_js var, which determines if the frontend JS
   * should be loaded.
   *
   * @return void
   */
  public function shortcode($atts, $content = null) {
    $this->enqueue_script();

    extract(shortcode_atts(array(
      'id'          => 0,
      'name'        => '',
      'action'      => 'link',
      'capability'  => '*'
    ), $atts));

    $output = '';

    // Shortcode does nothing if user doesn't have capability
    if ($capability !== '*') {
      if (!current_user_can($capability)) {
        return;
      }
    }

    // Shortcode action attribute requests a link to vote for the votable
    if ($action === 'link') {
      $id = (int)$id;
      if (!$id) {
        return '[missing votable id]';
      }

      if (!$content) {
        $content = 'Vote for #' . $id;
      }

      $output = '<div class="dkovotable" data-action="vote" data-votable-id="' . $id . '">';
      $output .= $content;
      $output .= '</div>';
      return $output;
    }

    // Shortcode action attribute requests a link to vote for the votable
    elseif ($action === 'count') {
      $id = (int)$id;
      if (!empty($id)) {
        $output = '<span class="dkovotable dkovotable-textcounter" data-votable-id="' . $id . '" data-action="count" data-count="' . $this->get_votable_count($id) . '">';
        $output .= $this->get_votable_count($id);
        $output .= '</span>';
      }
      elseif (!empty($name)) {
        $output = '<span class="dkovotable dkovotable-textcounter" data-votable-group-name="' . $name . '" data-action="count" data-count="' . $this->get_group_count($name) . '">';
        $output .= $this->get_group_count($name);
        $output .= '</span>';
      }
      else {
        return '[missing votable id or group name]';
      }
      return $output;
    }

    return $output;
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

  public function count() {
    $this->output_json(array(
      'success'     => true,
      'votes'       => $this->get_count()
    ));
    die(); // this is required to return a proper result
  }

  /**
   * vote
   * Hooked into wp_ajax_dkovotable_vote and wp_ajax_nopriv_dkovotable_vote
   * So AJAX calls to ajaxurl with action "dkovotable_vote" will use this function
   *
   * @access public
   * @return void
   */
  public function vote() {
    global $wpdb; // this is how you get access to the database

    // sanitize POST data
    $votable_id = (int)$_POST['votable_id'];
    if (!$votable_id) {
      $this->output_json(array('error' => 'Missing ID parameter.'));
      die();
    }

    // get current votes
    $sql = $wpdb->prepare("
      SELECT
        votables.votes_id AS votes_id,
        groups.name AS group_name,
        votes.votes AS votes
      FROM {$this->votables_table_name} AS votables
      LEFT JOIN {$this->votes_table_name} AS votes ON votables.votes_id = votes.id
      LEFT JOIN {$this->groups_table_name} AS groups ON votables.group_id = groups.id
      WHERE votables.id = %d
      ",
      $votable_id
    );
    $result = $wpdb->get_row($sql);

    if (!$result) {
      $this->output_json(array('error' => 'Votable ID not found.'));
      die();
    }

    $new_votes = $result->votes + 1;

    // increment votes
    $update = $wpdb->update(
      $this->votes_table_name,
      array('votes' => $new_votes),
      array('id' => $result->votes_id),
      array('%d'),
      array('%d')
    );

    if ($update === false) {
      $this->output_json(array('error' => 'Couldn\'t increment votes for vote with id ' . $result->votes_id));
      die();
    }

    if ($update === 0) {
      $this->output_json(array('error' => 'Nothing was updated trying to increment votes for vote with id ' . $result->votes_id));
      die();
    }

    $this->output_json(array(
      'success'     => true,
      'votable_id'  => $votable_id,
      'group_name'  => $result->group_name,
      'votes'       => $new_votes
    ));
    die(); // this is required to return a proper result
  }

}
$dkovotables = DKOVotables::get_instance();


/**
 * dkovotable_results
 * Pretty function wrapper for votable result counts
 *
 * @param string $type should be votable or group
 * @param int $id
 * @access public
 * @return int|bool false when $type is not votable or group
 */
function dkovotable_results($type, $id) {
  global $dkovotables;
  if ($type === 'votable') {
    return $dkovotables->get_votable_count($id);
  }
  elseif ($type === 'group') {
    return $dkovotables->get_group_count($id);
  }
  return false;
}
