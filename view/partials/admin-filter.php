      <h3>Filter</h3>
      <form method="POST" action="<?php echo $dkovotables->main_page; ?>">
        <p class="description">
          Pick some filters here to view a dashboard of results or export a report.
        </p>
        <fieldset>
          <label for="dkovotables-group-filter">Group</label>
          <select id="dkovotables-group-filter" name="filter_group">
            <?php foreach ($dkovotables->get_groups() as $group): ?>
              <option value="" <?php selected(0, $data['filter']['group']->id); ?>>ALL</option>
              <option value="<?php echo $group->id; ?>" <?php selected($group->id, $data['filter']['group']->id); ?>><?php echo $group->name; ?></option>
            <?php endforeach; ?>
          </select>

          <label for="dkovotables-limit-filter">Rows</label>
          <input id="dkovotables-limit-filter" name="filter_limit" type="number" value="<?php echo $data['filter']['limit']; ?>" max="500" min="1">

          <input type="submit" value="Filter" class="button action">
        </fieldset>
      </form>
