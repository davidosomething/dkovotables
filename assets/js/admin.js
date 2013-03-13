/*jshint
  browser:  true,
  devel:    true,
  jquery:   true
*/
/*global
*/
(function ($) {
  "use strict";

  $('.dkovotable').dkovotables({
    onlyClickOnce: false,
    removeAfterVote: false
  });

  $('.collapsibles .click-to-toggle').on('click', function () {
    $(this).toggleClass('active').next('.collapsible').toggle();
  });

})(jQuery);
