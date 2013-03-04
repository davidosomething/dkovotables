<?php
/**
 * Plugin Name: DKO Votables
 * Plugin URI:  http://github.com/davidosomething/dkovotables
 * Description: Voting framework for WordPress. Lets you create objects to vote on, quizzes, polls, posts, etc.
 * Author:      davidosomething
 * Version:     0.1.0
 * Author URI:  http://www.davidosomething.com/
 */

define('DKOVOTABLES_BASEPATH', __DIR__);
define('DKOVOTABLES_PLUGIN_FILE', __FILE__);

class DKOVotables
{

  const version = '0.1.0';

  public $default_options = array(
    'version' => '0.0.0',
  );
  private $slug;
  private $admin_page;

  public function __construct() {
    $this->slug = strtolower(basename(DKOVOTABLES_PLUGIN_FILE, '.php'));
    $this->admin_page = $this->slug . '/views/admin.php';

    $this->_setup_options();
    $this->ensure_version();

    register_activation_hook( DKOVOTABLES_PLUGIN_FILE, array( &$this, 'ensure_version' ) );
    add_action('admin_menu', array(&$this, 'add_root_menu'));
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
   * Update database tables associated to this plugin.
   *
   * @return void
   */
  private function _update_database() {
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
    add_action('admin_print_styles-' . $this->admin_page, array(&$this, 'admin_enqueue'));
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


}
$dkovotables = new DKOVotables();
