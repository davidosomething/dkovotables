<?php
/**
 * Plugin Name: DKO Votables
 * Plugin URI:  http://github.com/davidosomething/dkovotables
 * Description: Voting framework for WordPress. Lets you create objects to vote on, quizzes, polls, posts, etc.
 * Author:      davidosomething
 * Version:     0.1.5
 * Author URI:  http://www.davidosomething.com/
 */

class DKOVotables
{

  const version = '0.1.5';
  const slug = 'DKOVotables';
  const basepath = __DIR__;

  public $default_options = array(
    'version' => '0.0.0',
  );
  private $screen_id;
  public $votes_table_name;
  public $groups_table_name;
  public $votables_table_name;

  public function __construct() {
    global $wpdb;

    // initialize vars
    $this->screen_id = 'toplevel_page_' . self::slug;
    $this->votes_table_name = $wpdb->prefix . 'dkovotable_votes';
    $this->groups_table_name = $wpdb->prefix . 'dkovotable_groups';
    $this->votables_table_name = $wpdb->prefix . 'dkovotable_votes_to_groups';

    $this->_setup_options();

    // Make sure db is created/up-to-date
    register_activation_hook(__FILE__, array($this, 'ensure_version'));
    add_action('plugins_loaded', array($this, 'ensure_version'));

    // Add admin page and help
    add_action('admin_menu', array($this, 'add_root_menu'));
    add_filter('contextual_help', array($this, 'plugin_help'), 10, 3);
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
    if ($installed_version !== self::version) {
      $this->_update_database();
    }
  }

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

    $this->options['version'] = self::version;
    update_option('dkovotables_options', $this->options);
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
      self::slug,
      array($this, 'admin_page'),
      'http://s.gravatar.com/avatar/dcf949116994998753bd171a74f20fe9?s=16',
      100.001
    );
    add_action('admin_print_styles-' . $this->screen_id, array($this, 'admin_enqueue'));
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
   * admin_enqueue
   *
   * @access public
   * @return void
   */
  public function admin_enqueue() {
    wp_enqueue_style(self::slug, plugins_url('/assets/css/admin.css', __FILE__));
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

}
$dkovotables = new DKOVotables();
