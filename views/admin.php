<?php global $dkovotables; ?>
<div class="wrap">
  <div class="icon32"><br></div><h2>Votables</h2>

  <div class="container">
    <div class="main">
      <h3>Dashboard</h3>
      <p class="description">
        Pick some filters here to view a dashboard of results or export a report.
      </p>
    </div>

    <aside class="sidebar">
      <h3>Plugin info</h3>
      <dl>
        <dt>Installed version:</dt>
        <dd><?php echo $dkovotables::version; ?></dd>

        <dt>Support contact:</dt>
        <dd><a href="http://github.com/davidosomething/dkovotables/issues">Report issues on GitHub</a></dd>
        <dd><a href="<?php echo antispambot('me@davidosomething.com', true); ?>"><?php echo antispambot('me@davidosomething.com'); ?></a></dd>
        <dd>Twitter: <a href="http://twitter.com/davidosomething">@davidosomething</a></dd>
        <dd><a href="http://www.davidosomething.com">http://www.davidosomething.com</a></dd>
      </dl>
    </aside>
  </div>

</div>
