<?php
/**
 * Controller for the admin page
 */

global $wpdb;
global $dkovotables;
global $data;
global $output;

$data['filter'] = array();

$data['filter']['sort'] = empty($_POST['filter_sort']) ? 'id' : $_POST['filter_sort'];

// parse vars
$group_id = empty($_POST['filter_group']) ? 0 : (int)$_POST['filter_group'];
$data['filter']['group'] = $dkovotables->get_group('id', $group_id);

$data['filter']['limit'] = empty($_POST['filter_limit']) ? 100 : (int)$_POST['filter_limit'];
if ($data['filter']['limit'] < 1) {
  $data['filter']['limit'] = 1;
}
if ($data['filter']['limit'] > 500) {
  $data['filter']['limit'] = 500;
}

// load model, populates $data
include plugin_dir_path($this->plugin_file) . '/model/admin.php';

// load view
include plugin_dir_path($this->plugin_file) . '/view/admin.php';
