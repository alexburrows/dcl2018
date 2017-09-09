/**
 * @file
 * Custom js.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.dcl2017 = {
    attach: function(context, settings) {
      $(".col-md-4.list-item").click(function() {
        console.log('clicked');
        window.location = $(this).find(".field-content.speaker a").attr("href");
        return false;

      });
    }
  };

}(jQuery));