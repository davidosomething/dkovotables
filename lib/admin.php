<?php
abstract class DKOWPAdmin
{
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
      $this->menu_title, // title tags
      $this->menu_title, // on screen
      $this->menu_access,
      $this->slug,
      array($this, 'admin_page'),
      $this->menu_icon,
      $this->menu_position
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
    include plugin_dir_path($this->plugin_file) . '/controller/admin.php';
  }

  /**
   * admin_enqueue_scripts
   * Enqueue the admin side JS
   *
   * @access public
   * @return void
   */
  public function admin_enqueue_scripts($screen_id) {
    if ($screen_id !== $this->screen_id) {
      return;
    }
    // enqueue the main JS
    $this->enqueue_script();

    // enqueue the admin JS which relies on the main JS
    wp_enqueue_script(
      $this->slug . '-admin',
      plugins_url('/assets/js/admin.js', $this->plugin_file),
      array('jquery', $this->slug),
      $this->version,
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
      $this->slug . '-admin',
      plugins_url('/assets/css/admin.css', $this->plugin_file)
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
    include plugin_dir_path($this->plugin_file) . 'view/contextual_help.php';
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
}
