/**
 * BP Group Finder Admin JavaScript
 *
 * @package BP_Group_Finder
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  /**
   * BPGF Admin Metabox Handler
   */
  var BPGF_Admin_Metabox = {
    init: function () {
      this.bindEvents();
      this.initAutocomplete();
    },

    bindEvents: function () {
      var self = this;

      // Tag input keypress
      $(document).on("keypress", "#bpgf-interest-tags-input", function (e) {
        if (e.which === 13) {
          // Enter key
          e.preventDefault();
          self.addTag($(this).val().trim());
          $(this).val("");
        }
      });

      // Remove tag buttons
      $(document).on("click", ".bpgf-remove-tag", function (e) {
        e.preventDefault();
        $(this).closest(".bpgf-tag-chip").remove();
        self.updateHiddenField();
      });

      // Popular tags
      $(document).on("click", ".bpgf-add-popular-tag", function (e) {
        e.preventDefault();
        var tag = $(this).data("tag");
        self.addTag(tag);
      });
    },

    initAutocomplete: function () {
      var self = this;

      $("#bpgf-interest-tags-input").autocomplete({
        source: function (request, response) {
          $.ajax({
            url: bpgfAdmin.ajaxUrl,
            type: "POST",
            data: {
              action: "bpgf_autocomplete_tags",
              nonce: bpgfAdmin.nonce,
              term: request.term,
            },
            success: function (data) {
              if (data.success) {
                response(data.data);
              }
            },
          });
        },
        minLength: 2,
        select: function (event, ui) {
          self.addTag(ui.item.value);
          $(this).val("");
          return false;
        },
      });
    },

    addTag: function (tag) {
      if (!tag || this.tagExists(tag)) {
        return;
      }

      if (this.getTagCount() >= bpgfAdmin.maxTags) {
        alert(bpgfAdmin.strings.maxTagsReached);
        return;
      }

      var tagChip = $(
        '<span class="bpgf-tag-chip" data-tag="' +
          tag +
          '">' +
          tag +
          '<button type="button" class="bpgf-remove-tag" aria-label="' +
          bpgfAdmin.strings.removeTag +
          '">&times;</button>' +
          "</span>",
      );

      $("#bpgf-current-tags").append(tagChip);
      this.updateHiddenField();
    },

    tagExists: function (tag) {
      var exists = false;
      $(".bpgf-tag-chip").each(function () {
        if ($(this).data("tag").toLowerCase() === tag.toLowerCase()) {
          exists = true;
          return false;
        }
      });
      return exists;
    },

    getTagCount: function () {
      return $(".bpgf-tag-chip").length;
    },

    updateHiddenField: function () {
      var tags = [];
      $(".bpgf-tag-chip").each(function () {
        tags.push($(this).data("tag"));
      });
      $("#bpgf-interest-tags-hidden").val(tags.join(","));
    },
  };

  // Initialize when document is ready
  $(document).ready(function () {
    BPGF_Admin_Metabox.init();
  });
})(jQuery);
