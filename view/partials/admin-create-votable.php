<h3>Create a new Votable</h3>
<form method="POST" action="<?php echo $dkovotables->main_page; ?>">
  <?php wp_nonce_field('create_votable', $dkovotables::SLUG); ?>
  <p class="description">
    Create a new votable.
  </p>
  <table class="form-table">
    <tr valign="top">
      <th scope="row"><label for="dkovotables-create-description">Description<span class="required">*</span></label></th>
      <td><textarea id="dkovotables-create-description" name="create_votable_description" rows="2" cols="30" required></textarea></td>
    </tr>
    <tr valign="top">
      <th scope="row"><label for="dkovotables-create-group">Group</label></th>
      <td><select id="dkovotables-create-group" name="create_votable_group">
          <option value="">ALL</option>
          <?php foreach ($dkovotables->get_groups() as $group): ?>
            <option value="<?php echo $group->id; ?>"><?php echo $group->name; ?></option>
          <?php endforeach; ?>
      </select></td>
    </tr>
    <tr valign="top">
      <th scope="row"><label for="dkovotables-create-votes">Votes (optional)</label></th>
      <td><input id="dkovotables-create-votes" name="create_votable_votes" type="number" value="0" min="0"></td>
    </tr>
  </table>
  <p class="submit"><input type="submit" value="Save" class="button button-primary"></p>
</form>
