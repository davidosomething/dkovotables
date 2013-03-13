<?php
/**
 * Controller for the admin page
 */

global $wpdb;
global $dkovotables;
global $data;
global $output;

$data['filter'] = array();

// parse vars
$group_id = empty($_POST['filter_group']) ? 0 : (int)$_POST['filter_group'];
$data['filter']['group'] = $dkovotables->get_group($group_id);

$data['filter']['limit'] = empty($_POST['filter_limit']) ? 10 : (int)$_POST['filter_limit'];
if ($data['filter']['limit'] < 1) {
  $data['filter']['limit'] = 1;
}
if ($data['filter']['limit'] > 500) {
  $data['filter']['limit'] = 500;
}

// load model, populates $data
include $dkovotables::BASEPATH . '/model/admin.php';

// load view
include $dkovotables::BASEPATH . '/view/admin.php';
