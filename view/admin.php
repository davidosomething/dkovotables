<?php
/**
 * Main view for the admin page
 */
?>
<div class="wrap">
  <?php screen_icon(); ?><h2>Votables</h2>

  <div class="container">
    <div class="main">
      <div class="collapsibles">
        <?php include 'partials/admin-create-votable.php'; ?>
        <hr>
        <?php include 'partials/admin-create-group.php'; ?>
        <hr>
      </div>
    </div>
    <?php include 'partials/admin-sidebar.php' ?>

    <div class="clear"></div>

    <div class="results">
      <?php include 'partials/admin-filter.php'; ?>

      <h3>Votes in the group <em><?php echo $data['filter']['group']->name; ?></em></h3>
      <p class="description"><?php echo $data['filter']['group']->description; ?></p>
      <p><strong>Total rows</strong>: <?php echo count($data['votes']); ?>

      <table class="wp-list-table widefat fixed posts" cellspacing="0">
        <thead>
          <?php ob_start(); ?>
          <tr>
            <th scope="col" class="manage-column id"><span>ID</span></th>
            <th scope="col" class="manage-column"><span>Description</span></th>
            <th scope="col" class="manage-column group"><span>Group</span></th>
            <th scope="col" class="manage-column votes"><span>Votes</span></th>
            <th scope="col" class="manage-column vote-for"><span>Vote for this</span></th>
          </tr>
          <?php $table_header = ob_get_contents(); ob_end_flush(); ?>
        </thead>
        <tfoot>
          <?php echo $table_header; ?>
        </tfoot>

        <tbody>
          <?php $index = 0; foreach ($data['votes'] as $votable): ?>
            <tr class="<?php if ($index % 2) echo 'alternate'; ?>" valign="top">
              <td class="id"><?php echo $votable->id; ?></td>
              <td>
                <?php echo $votable->description; ?><br>
                <code>[dkovotable action="link" id="<?php echo $votable->id; ?>"]</code>
              </td>
              <td class="group"><?php echo $votable->group_name; ?></td>
              <td class="votes">
                <?php echo do_shortcode('[dkovotable action="count" id="' . $votable->id . '"]'); ?><br>
                [<a href="<?php echo $this->admin_link('reset', $votable->id); ?>">reset</a>]
              </td>
              <td class="vote-for">
                <?php echo do_shortcode('[dkovotable action="link" id="' . $votable->id . '"]'); ?><br>
                [<a href="<?php echo $this->admin_link('delete', $votable->id); ?>">delete</a>]
              </td>
            </tr>
          <?php $index += 1; endforeach; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
