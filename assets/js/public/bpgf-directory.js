/**
 * BP Group Finder Public JavaScript
 *
 * @package BP_Group_Finder
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  /**
   * BPGF Directory Handler
   */
  var BPGF_Directory = {
    init: function () {
      this.bindEvents();
    },

    bindEvents: function () {
      var self = this;

      // Tag chips in directory
      $(document).on("click", ".bpgf-tag-chip", function (e) {
        e.preventDefault();
        var tag = $(this).data("tag");
        self.filterByTag(tag);
      });
    },

    filterByTag: function (tag) {
      // Update URL with tag filter
      var currentUrl = window.location.href;
      var separator = currentUrl.indexOf("?") !== -1 ? "&" : "?";
      var newUrl =
        currentUrl + separator + "interest=" + encodeURIComponent(tag);

      // Redirect to filtered results
      window.location.href = newUrl;
    },
  };

  // Initialize when document is ready
  $(document).ready(function () {
    BPGF_Directory.init();
  });
})(jQuery);
