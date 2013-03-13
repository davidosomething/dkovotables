<?php
/**
 * Main view for the admin page
 */
?>
<div class="wrap">
  <div class="icon32"><br></div><h2>Votables</h2>

  <div class="container">
    <div class="main">

      <?php include 'partials/admin-create-votable.php'; ?>
      <?php include 'partials/admin-create-group.php'; ?>

      <div class="clear"></div><hr>

      <?php include 'partials/admin-filter.php'; ?>

      <h3>Votes in the group <em><?php echo $data['filter']['group']->name; ?></em></h3>
      <p class="description"><?php echo $data['filter']['group']->description; ?></p>
      <p><strong>Total rows</strong>: <?php echo count($data['votes']); ?>

      <table class="wp-list-table widefat fixed posts" cellspacing="0">
        <thead>
          <?php ob_start(); ?>
          <tr>
            <th scope="col" class="manage-column"><span>ID</span></th>
            <th scope="col" class="manage-column"><span>Description</span></th>
            <th scope="col" class="manage-column"><span>Group</span></th>
            <th scope="col" class="manage-column"><span>Votes</span></th>
            <th scope="col" class="manage-column"><span>Vote for this</span></th>
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
              <td><div class="votes"><?php echo do_shortcode('[dkovotable action="count" id="' . $vote->id . '"]'); ?></div></td>
              <td><div class="vote-for"><?php echo do_shortcode('[dkovotable action="link" id="' . $vote->id . '"]'); ?></div></td>
            </tr>
          <?php $index += 1; endforeach; ?>
        </tbody>
      </table>

    </div>
    <?php include 'partials/admin-sidebar.php' ?>
  </div>
</div>
