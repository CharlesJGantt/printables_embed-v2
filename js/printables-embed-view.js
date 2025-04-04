/**
 * @file
 * JavaScript for the Printables Embed when viewing content.
 */
(function (Drupal) {
  'use strict';

  /**
   * Behavior for Printables Embed processing.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.printablesEmbed = {
    attach: function (context, settings) {
      // Process embeds that need client-side enhancement
      const embeds = context.querySelectorAll('.printables-embed[data-printables-id]');
      
      embeds.forEach(embed => {
        // Ensure we only process each embed once
        if (embed.hasAttribute('data-processed')) {
          return;
        }
        
        // Mark as processed to avoid duplicate processing
        embed.setAttribute('data-processed', 'true');
        
        // Add click event to make entire embed clickable if desired
        embed.addEventListener('click', function(e) {
          // Only handle click if it's not on an existing link
          if (!e.target.closest('a')) {
            const viewButton = embed.querySelector('.view-button');
            if (viewButton) {
              // Get the URL from the view button and navigate to it
              const url = viewButton.getAttribute('href');
              if (url) {
                window.open(url, '_blank');
              }
            }
          }
        });
        
        // Add visual cue that the embed is clickable
        embed.style.cursor = 'pointer';
      });
    }
  };

})(Drupal);