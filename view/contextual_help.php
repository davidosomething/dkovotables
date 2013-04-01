<h2 id="usage">Usage</h2>

<p>The Votables plugin creates a new table in your WordPress blog for storing
votes.<br>
Anything can be voted on, whether it is a post, custom post type, arbitrary
item, URL, image, etc.</p>

<h3 id="creatingvotables">Creating Votables</h3>

<p>To define something new that can be voted on, create a new Votable.<br>
Then, to create a link to vote on that thing, use the shortcode:</p>

<pre>[dkovotable action=&quot;link&quot; id=&quot;THINGID&quot;]</pre>

<p>Both versions will generate a unique, one-time use link to vote on that
thing. The group attribute/second function parameter is optional.</p>

<h3 id="displayingresults">Displaying Results</h3>

<p>When you create a Votable link, you can specify a group for that votable.
That allow you to aggregate results for that votable. Otherwise votes will
only count toward total votes for that Votable. Here are the shortcodes:</p>

<ul>
<li>All votes for THING_ID: <pre>[dkovotable action=&quot;count&quot; id=&quot;THINGID&quot;]</pre></li>
</ul>

<p>Or use the function forms:</p>

<pre>&lt;?php
echo dkovotable_results('votable', $thing_id);
echo dkovotable_results('group', &apos;COOL_THINGS&apos;);
?&gt;</pre>
