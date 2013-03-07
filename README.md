DKO Votables
============

* **Plugin URI:**  [http://github.com/davidosomething/dkovotables](http://github.com/davidosomething/dkovotables)
* **Description:** Voting framework for WordPress. Lets you create objects to vote on, quizzes, polls, posts, etc.
* **Author:**      davidosomething
* **Author URI:**  [http://www.davidosomething.com](http://www.davidosomething.com)


TODO
----

* Add new votable, text fields for desc, group
* Vote on votable via link
  * check that votable exists
  * then redirect to `return_url` param, otherwise exit
* AJAX for voting on votable
    * JS for disabling voting link once used
* nonce voting via `wp_nonce_url`
    * `wp_verify_nonce` backend
* Submittable filter form

### Phase 2 ###

* Clear votes for votable V (update where id = V)
* Delete votes for X
  * Delete votables where vote_id = X
* Delete group G
  * Select list of vote_ids from votables where group_id = G
  * Delete votes where id in (votables.vote_id)
  * Delete votables where group_id = G

### Phase 3 ###

* Add new votable: group field autocomplete
* Manually edit votes
* Edit vote item description
* Edit group description
* Sort results table by clicking column headers
