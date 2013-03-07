<h2 id="usage">Usage</h2>

<p>The Votables plugin creates a new table in your WordPress blog for storing
votes.<br>
Anything can be voted on, whether it is a post, custom post type, arbitrary
item, URL, image, etc.</p>

<h3 id="creatingvotables">Creating Votables</h3>

<p>To define something new that can be voted on, create a new Votable.<br>
Then, to create a link to vote on that thing, use the shortcode:</p>

<pre>[dkovotable-vote-link id=&quot;THINGID&quot;]
[dkovotable-vote-link id=&quot;THINGID&quot; group=&quot;COOL_THINGS&quot;]</pre>

<p>Or you can use the PHP function:</p>

<pre>&lt;?php echo dkovotable_vote_link($thing_id, &apos;cool_things&apos;); ?&gt;</pre>

<p>Both versions will generate a unique, one-time use link to vote on that
thing. The group attribute/second function parameter is optional.</p>

<h3 id="displayingresults">Displaying Results</h3>

<p>When you create a Votable link, you can specify a group for that votable.
That allow you to aggregate results for that votable. Otherwise votes will
only count toward total votes for that Votable. Here are the shortcodes:</p>

<ul>
<li>All votes for THING_ID: <pre>[dkovotable-results id=&quot;THINGID&quot;]</pre></li>
<li>Votes for THING_ID in the COOL_THINGS group: <pre>[dkovotable-results id=&quot;THINGID&quot; group=&quot;COOL_THINGS&quot;]</pre></li>
<li>Votes for all things in the COOL_THINGS group: <pre>[dkovotable-results group=&quot;COOL_THINGS&quot;]</pre></li>
</ul>

<p>Or use the function forms:</p>

<pre>&lt;?php
echo dkovotable_results($thing_id);
echo dkovotable_results($thing_id, &apos;COOL_THINGS&apos;);
echo dkovotable_results(null, &apos;COOL_THINGS&apos;);
?&gt;</pre>
