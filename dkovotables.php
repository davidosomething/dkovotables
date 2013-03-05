<?php
/**
 * Plugin Name: DKO Votables
 * Plugin URI:  http://github.com/davidosomething/dkovotables
 * Description: Voting framework for WordPress. Lets you create objects to vote on, quizzes, polls, posts, etc.
 * Author:      davidosomething
 * Version:     0.1.1
 * Author URI:  http://www.davidosomething.com/
 */

define('DKOVOTABLES_BASEPATH', __DIR__);
define('DKOVOTABLES_PLUGIN_FILE', __FILE__);

class DKOVotables
{

  const version = '0.1.1';

  public $default_options = array(
    'version' => '0.0.0',
  );
  private $slug;
  private $admin_page;

  public function __construct() {
    $this->slug = strtolower(basename(DKOVOTABLES_PLUGIN_FILE, '.php'));
    $this->screen_id = $this->slug . '/views/admin';
    $this->admin_page = $this->screen_id . '.php';

    $this->_setup_options();

    // Make sure db is created/up-to-date
    register_activation_hook(DKOVOTABLES_PLUGIN_FILE, array($this, 'ensure_version'));
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
    $votes_table_name = $wpdb->prefix . 'dkovotable_votes';
    $sql = "CREATE TABLE $votes_table_name (
      id          mediumint(9)                                NOT NULL AUTO_INCREMENT,
      votes       mediumint(9)  DEFAULT 0                     NOT NULL,
      group_id    mediumint(9)  DEFAULT 0                     NOT NULL,
      description text                                        NOT NULL,
      create_date datetime      DEFAULT '0000-00-00 00:00:00' NOT NULL,
      UNIQUE KEY id (id)
    );";
    dbDelta($sql);

    // Create/update the groups table
    $groups_table_name = $wpdb->prefix . 'dkovotable_groups';
    $sql = "CREATE TABLE $groups_table_name (
      id          mediumint(9)                                NOT NULL AUTO_INCREMENT,
      group_name  varchar(255)  DEFAULT ''                    NOT NULL,
      description text                                        NOT NULL,
      create_date datetime      DEFAULT '0000-00-00 00:00:00' NOT NULL,
      UNIQUE KEY id (id)
    );";
    dbDelta($sql);

    // Create/update the votables-groups relationship table
    $votables_to_groups_table_name = $wpdb->prefix . 'dkovotable_votables_to_groups';
    $sql = "CREATE TABLE $votables_to_groups_table_name (
      id          mediumint(9)                                NOT NULL AUTO_INCREMENT,
      votable_id  mediumint(9)  DEFAULT 0                     NOT NULL,
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
      $this->admin_page,
      '',
      'http://s.gravatar.com/avatar/dcf949116994998753bd171a74f20fe9?s=16',
      100.001
    );
    add_action('admin_print_styles-' . $this->admin_page, array($this, 'admin_enqueue'));
  }

  /**
   * admin_enqueue
   *
   * @access public
   * @return void
   */
  public function admin_enqueue() {
    wp_enqueue_style(
      $this->slug,
      plugins_url('/assets/css/admin.css', DKOVOTABLES_PLUGIN_FILE)
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
    include plugin_dir_path(__FILE__) . 'views/contextual_help.php';
    $contextual_help = ob_get_contents();
    ob_end_clean();
    return $contextual_help;
  }

}
$dkovotables = new DKOVotables();
