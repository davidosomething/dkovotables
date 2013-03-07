<?php
/**
 * Main view for the admin page
 */
?>
<div class="wrap">
  <div class="icon32"><br></div><h2>Votables</h2>

  <div class="container">
    <div class="main">
      <h3>Filter</h3>
      <form method="GET">
        <?php wp_nonce_field(); ?>
        <p class="description">
          Pick some filters here to view a dashboard of results or export a report.
        </p>
        <fieldset>
          <label for="dkovotables-group-filter">Group</label>
          <select id="dkovotables-group-filter" name="group">
            <?php foreach ($data['groups'] as $group): ?>
              <option value="">ALL</option>
              <option value="<?php echo $group->id; ?>" <?php selected($group->id, $data['group_id']); ?>><?php echo $group->name; ?></option>
            <?php endforeach; ?>
          </select>

          <input type="submit">
        </fieldset>
      </form>

      <h3>Votes in the group <em><?php echo $data['group']; ?></em></h3>
      <p class="description"><?php echo $data['group_description']; ?></p>
      <p><strong>Total rows</strong>: <?php echo count($data['votes']); ?>

      <table class="wp-list-table widefat fixed posts" cellspacing="0">
        <thead>
          <?php ob_start(); ?>
          <tr>
            <th scope="col" class="manage-column"><span>ID</span></th>
            <th scope="col" class="manage-column"><span>Description</span></th>
            <th scope="col" class="manage-column"><span>Group</span></th>
            <th scope="col" class="manage-column"><span>Votes</span></th>
          </tr>
          <?php $table_header = ob_get_contents(); ob_end_flush(); ?>
        </thead>
        <tfoot>
          <?php echo $table_header; ?>
        </tfoot>

        <tbody>
          <?php $index = 0; foreach ($data['votes'] as $vote): ?>
            <tr class="<?php if ($index % 2) echo 'alternate'; ?>" valign="top">
              <td><div class="id"><?php echo $vote->id; ?></div></td>
              <td><div class="description"><?php echo $vote->description; ?></div></td>
              <td><div class="group"><?php echo $vote->group_name; ?></div></td>
              <td><div class="votes"><?php echo $vote->votes; ?></div></td>
            </tr>
          <?php $index += 1; endforeach; ?>
        </tbody>
      </table>

    </div>
    <?php include 'admin-sidebar.php' ?>
  </div>
</div>
