/**
 * @file
 * JavaScript to process Printables embeds on the client side.
 */
(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.printablesEmbed = {
    attach: function (context, settings) {
      // Find all printables embeds that haven't been processed yet
      $(once('printables-embed', 'div.printables-embed:not(.printables-processed)', context)).each(function() {
        const $embed = $(this);
        const modelId = $embed.attr('data-printables-id');
        
        if (modelId) {
          // Add processed class
          $embed.addClass('printables-processed');
          
          // Fetch data from server
          $.ajax({
            url: Drupal.url('printables-embed/fetch/' + modelId),
            type: 'GET',
            dataType: 'json',
            success: function(data) {
              if (data.html) {
                // Replace the placeholder with the rendered embed
                $embed.replaceWith(data.html);
              }
            },
            error: function() {
              console.error('Failed to load Printables embed data for model ' + modelId);
            }
          });
        }
      });
    }
  };
})(jQuery, Drupal);