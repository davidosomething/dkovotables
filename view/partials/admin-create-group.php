<div class="collapsibles half">
  <h3 class="click-to-toggle">Create a new Group <span class="description">(click to toggle)</span></h3>
  <div class="collapsible">
    <form method="POST" action="<?php echo $dkovotables->main_page; ?>">
      <?php wp_nonce_field(); ?>
      <p class="description">
        Create a new group.
      </p>
      <table class="form-table">
        <tr valign="top">
          <th scope="row"><label for="dkovotables-create-group-name">Name</label></th>
          <td><input id="dkovotables-create-group-name" name="create_group_name" type="text" required></td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="dkovotables-create-group-description">Description</label></th>
          <td><textarea id="dkovotables-create-group-description" name="create_group_description" rows="2" cols="30"></textarea></td>
        </tr>
      </table>
      <p class="submit"><input type="submit" value="Save" class="button button-primary"></p>
    </form>
  </div>
</div>