<h3>Create a new Group</h3>
<form method="POST" action="<?php echo $dkovotables->main_page; ?>">
  <?php wp_nonce_field('create_group', $dkovotables::SLUG); ?>
  <p class="description">
    Create a new group.
  </p>
  <table class="form-table">
    <tr valign="top">
      <th scope="row"><label for="dkovotables-create-group-name">Name<span class="required">*</span></label></th>
      <td><input id="dkovotables-create-group-name" name="create_group_name" type="text" required></td>
    </tr>
    <tr valign="top">
      <th scope="row"><label for="dkovotables-create-group-description">Description<span class="required">*</span></label></th>
      <td><textarea id="dkovotables-create-group-description" name="create_group_description" rows="2" cols="30"></textarea></td>
    </tr>
  </table>
  <p class="submit"><input type="submit" value="Save" class="button button-primary"></p>
</form>
