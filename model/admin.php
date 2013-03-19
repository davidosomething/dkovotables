<?php
/**
 * Model for the Admin page's results section
 */

$order = 'ORDER BY votes.id ASC';
if ($data['filter']['sort'] === 'votes') {
  $order = 'ORDER BY votes.votes DESC';
}

// get results
if (empty($data['filter']['group']->id)) {
  $prepared_sql = $wpdb->prepare(
    "
    SELECT
      votables.id AS id,
      groups.name AS group_name,
      votes.description AS description,
      votes.votes AS votes
    FROM
      {$dkovotables->votables_table_name} AS votables
    LEFT JOIN {$dkovotables->votes_table_name} AS votes ON (votables.votes_id = votes.id)
    LEFT JOIN {$dkovotables->groups_table_name} AS groups ON (votables.group_id = groups.id)
    {$order}
    LIMIT %d
    ",
    $data['filter']['limit']
  );
}

else {
  $prepared_sql = $wpdb->prepare(
    "
    SELECT
      votables.id AS id,
      groups.name AS group_name,
      votes.description AS description,
      votes.votes AS votes
    FROM
      {$dkovotables->votables_table_name} AS votables,
      {$dkovotables->groups_table_name} AS groups,
      {$dkovotables->votes_table_name} AS votes
    WHERE groups.id = %d
      AND votables.group_id = groups.id
      AND votes.id = votables.votes_id
    {$order}
    LIMIT %d
    ",
    $data['filter']['group']->id,
    $data['filter']['limit']
  );
}
$data['votes'] = $wpdb->get_results($prepared_sql);
$this->cache_votes($data['votes']);
