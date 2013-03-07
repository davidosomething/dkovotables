<?php
/**
 * Controller for the admin page
 */

global $wpdb;
global $dkovotables;
global $data;
global $output;

// parse vars
$data['group'] = 'ALL';
$data['group_id'] = 0;
$data['group_description'] = '100 votables from all groups';

// @debug values
$data['group'] = 'test';
$data['group_id'] = 1;
$data['group_description'] = 'Test group';
// @debug values end

// load model, populates $data
include $dkovotables::basepath . '/model/admin.php';

// load view
include $dkovotables::basepath . '/view/admin.php';
