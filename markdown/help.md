Usage
-----

The Votables plugin creates a new table in your WordPress blog for storing
votes.<br>
Anything can be voted on, whether it is a post, custom post type, arbitrary
item, URL, image, etc.

### Creating Votables ###

To define something new that can be voted on, create a new Votable.<br>
Then, to create a link to vote on that thing, use the shortcode:
```
[dkovotable-vote-link id="THINGID"]
[dkovotable-vote-link id="THINGID" group="COOL_THINGS"]
```

Or you can use the PHP function:
```
<?php echo dkovotable_vote_link($thing_id, 'cool_things'); ?>
```

Both versions will generate a unique, one-time use link to vote on that
thing. The group attribute/second function parameter is optional.

### Displaying Results ###

When you create a Votable link, you can specify a group for that votable.
That allow you to aggregate results for that votable. Otherwise votes will
only count toward total votes for that Votable. Here are the shortcodes:

* All votes for THING_ID: `[dkovotable-results id="THINGID"]`
* Votes for THING_ID in the COOL_THINGS group: `[dkovotable-results id="THINGID" group="COOL_THINGS"]`
* Votes for all things in the COOL_THINGS group: `[dkovotable-results group="COOL_THINGS"]`

Or use the function forms:
```
<?php
echo dkovotable_results($thing_id);
echo dkovotable_results($thing_id, 'COOL_THINGS');
echo dkovotable_results(null, 'COOL_THINGS');
?>
```
