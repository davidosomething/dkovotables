<?php
/**
 * Model for the Admin page's results section
 */

// get results
if (empty($data['filter']['group']->id)) {
  $prepared_sql = $wpdb->prepare(
    "
    SELECT
      groups.name AS group_name,
      votes.id AS id,
      votes.description AS description,
      votes.votes AS votes
    FROM
      {$dkovotables->votes_table_name} AS votes
    LEFT JOIN {$dkovotables->votables_table_name} AS votables ON (votes.id = votables.votes_id)
    LEFT JOIN {$dkovotables->groups_table_name} AS groups ON (votables.group_id = groups.id)
    LIMIT %d
    ",
    $data['filter']['limit']
  );
}

else {
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
    WHERE groups.id = %d
      AND votables.group_id = groups.id
      AND votes.id = votables.votes_id
    LIMIT %d
    ",
    $data['filter']['group']->id,
    $data['filter']['limit']
  );
}
$data['votes'] = $wpdb->get_results($prepared_sql);
$this->cache_votes($data['votes']);
