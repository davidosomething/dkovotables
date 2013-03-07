<?php
/**
 * Model for the Admin page's results section
 */

// get groups for filter dropdown
$sql = "SELECT id, name FROM {$dkovotables->groups_table_name}";
$data['groups'] = $wpdb->get_results($sql);

$group_names = wp_list_pluck($data['groups'], 'name');

// get results
if ($data['group'] === 'ALL') {
  $prepared_sql = $wpdb->prepare(
    "
    SELECT * FROM {$dkovotables->votes_table_name}
    LIMIT %d
    ",
    10
  );
}

//
elseif (in_array($data['group'], $group_names)) {
  $prepared_sql = $wpdb->prepare(
    "
    SELECT
      groups.name AS group_name,
      votes.id AS id,
      votes.description AS description,
      votes.votes AS votes
    FROM
      {$dkovotables->groups_table_name} AS groups,
      {$dkovotables->votables_table_name} AS votables,
      {$dkovotables->votes_table_name} AS votes
    WHERE groups.name = %s
      AND votables.group_id = groups.id
      AND votes.id = votables.votes_id
    LIMIT %d
    ",
    $data['group'],
    10
  );
}
$data['votes'] = $wpdb->get_results($prepared_sql);
