/*jshint
  browser:  true,
  devel:    true,
  jquery:   true
*/
/*global
  ajaxurl:  true
*/
(function ($) {
  "use strict";

  $.fn.dkovotables = function (options) {
    var defaults;

    var ajaxurl = window.ajaxurl || DKOVotables.ajaxurl;
    var els = this;
    var $els = $(els);
    var $counters;

////////////////////////////////////////////////////////////////////////////////
// Methods /////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
    var methods = {};

    /**
     * methods.addCounter
     * use if you create new counter dom elements
     */
    methods.addCounter = function ($newCounters) {
      $els = $els.add($newCounters);
      els = $els.get();
      methods.getCounters();
    };

    /**
     * methods.reloadCounters
     * @return int number of counters found
     */
    methods.getCounters = function () {
      return $els.filter('[data-action="count"]');
    };

    /**
     * methods.updateCounters
     * change counter values
     */
    methods.updateCounters = function (actions) {
      var i = 0;
      var totalActions = actions.length;
      var $matchingCounters, $counter, count;

      if (!totalActions) {
        return;
      }

      // iterate through updating data
      for (; i < totalActions; i++) {
        $matchingCounters = $counters.filter('[data-votable-id="' + actions[i].votable_id + '"]');

        if (actions[i].change === 'set') {
          count = actions[i].votes;
        }

        else if (['increment', 'decrement'].indexOf(actions[i].change)) {
          // do math on only one
          $counter = $matchingCounters.first();
          count = parseInt($counter.data('count'), 10); // make sure the count is an int

          if (actions[i].change === 'increment') {
            count = count + 1;
          }
          else if (actions[i].change === 'decrement') {
            count = count - 1;
          }
        }

        // update jquery data (and view if necessary)
        $matchingCounters.data('count', count).filter('.dkovotable-textcounter').text(count);
      }
    };

    /**
     * methods.clickedVotable
     * What to do when a votable is clicked
     */
    methods.clickedVotable = function (e) {
      var votable = this;
      var $this;
      var votableId;
      e.preventDefault();

      $this = $(this);

      if (typeof options.afterClick === 'function') {
        options.onClick.call(votable);
      }

      votableId = $this.data('votable-id');
      if (!votableId) {
        console.error('votable missing id');
      }

      // AJAX vote
      var vote = $.ajax({
        url:  ajaxurl,
        type: 'POST',
        data: {
          action: 'dkovotable_vote',
          votable_id: votableId
        }
      });

      vote.always($.proxy(methods.onClick, votable));
      vote.done($.proxy(methods.onDone, votable));
      vote.fail($.proxy(options.onFail, votable));
    };

    /**
     * methods.onClick
     * Default AJAX handler for always, call via proxy
     */
    methods.onClick = function () {
      var votable = this;
      if (options.onlyClickOnce) {
        methods.disableVotable.call(votable);
      }

      // custom handler
      if (typeof options.onClick === 'function') {
        options.onClick.call(votable);
      }
    };

    /**
     * methods.onDone
     * Default AJAX handler for voting, call via proxy
     * @param object response
     */
    methods.onDone = function (response) {
      var votable = this;

      if (response.error) {
        console.error(response.error);
        return;
      }

      // option to remove element
      if (options.removeAfterVote) {
        methods.removeVotable.call(votable);
      }

      // option to update counters
      if (options.updateCountersAfterVote) {
        methods.updateCounters([
          { change: 'set', votable_id: response.votable_id, votes: response.votes }
        ]);
      }

      // custom handler
      if (typeof options.onDone === 'function') {
        options.onDone.call(votable);
      }
    };

    /**
     * methods.error
     * Default success handler for voting
     */
    methods.voteError = function (response) {
      console.error(response);
    };

    /**
     * methods.disableVotable
     * Disable consecutive clicks
     */
    methods.disableVotable = function () {
      $(this).off('click', methods.clickedVotable);
    };

    /**
     * methods.removeVotable
     * Default action to take after clicking a votable
     */
    methods.removeVotable = function () {
      $(this).remove();
    };

////////////////////////////////////////////////////////////////////////////////
// Options /////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
    defaults = {
      onlyClickOnce: true,            // prevent multiple clicks on a votable
      removeAfterVote: true,          // remove the votable element after it is clicked
      updateCountersAfterVote: true,  // update known counters after clicking vote
      onDone: null,                   // fires after successful vote (AJAX success)
      onError: null,                  // fires after unsuccessful vote (AJAX success)
      onFail: methods.ajaxFail        // fires on AJAX errors
    };
    options = $.extend(defaults, options);

    console.log(options);

////////////////////////////////////////////////////////////////////////////////
// Plugin Body /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

    $counters = methods.getCounters();

    /* Iterate over dkovotables */
    return this.each(function () {
      var $this = $(this);

      var action = $this.data('action');
      if (!action) {
        action = 'count';
      }

      if (action === 'vote') {
        $this.on('click', methods.clickedVotable);
      }

    });
  };

})(jQuery);
