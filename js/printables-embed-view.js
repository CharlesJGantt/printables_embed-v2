/**
 * @file
 * JavaScript for the Printables Embed when viewing content.
 */
(function (Drupal, drupalSettings, once) {
  'use strict';

  /**
   * Behavior for Printables Embed processing.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.printablesEmbed = {
    attach: function (context, settings) {
      // Get model data from drupalSettings
      const models = drupalSettings.printablesEmbed ? drupalSettings.printablesEmbed.models || {} : {};
      
      // Process embeds that need client-side enhancement
      once('printables-embed', '[data-printables-embed]', context).forEach(placeholder => {
        const modelId = placeholder.getAttribute('data-printables-embed');
        
        // If we have data for this model ID
        if (modelId && models[modelId]) {
          const model = models[modelId];
          
          // Create the embed HTML
          const embed = this.createEmbedHTML(model, modelId);
          
          // Replace the placeholder with the embed
          placeholder.outerHTML = embed;
        }
      });
    },
    
    /**
     * Creates the HTML for a Printables embed.
     *
     * @param {Object} model - The model data
     * @param {string} modelId - The model ID
     * @return {string} The HTML for the embed
     */
    createEmbedHTML: function(model, modelId) {
      // Get the path to the logo image
      const logoPath = drupalSettings.path.baseUrl + 'modules/printables_embed/images/printables-logo.png';
      
      // Create the HTML
      return `
        <div class="printables-embed" data-processed="true">
          <div class="thumbnail" style="background-image: url('${model.imageUrl}');"></div>
          <div class="content">
            <div class="header">
              <h2 class="model-name" title="${model.name}">
                ${model.name.length > 33 ? model.name.substring(0, 30) + '...' : model.name}
              </h2>
              <a href="${model.modelUrl}" target="_blank" class="logo-container">
                <img src="${logoPath}" alt="Printables" class="printables-logo-img">
              </a>
            </div>
            <div class="author">
              <img class="author-avatar" src="${model.authorAvatar}" alt="${model.author}">
              by ${model.author}
            </div>
            ${model.summary ? `<div class="summary">${model.summary}</div>` : ''}
            <div class="stats-container">
              <div class="stats">
                <div class="stat">
                  <svg class="stat-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                  </svg>
                  ${model.likesCount}
                </div>
                <div class="stat">
                  <svg class="stat-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z" />
                  </svg>
                  ${model.downloadCount}
                </div>
                <div class="stat">
                  <svg class="stat-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" />
                  </svg>
                  ${model.viewCount}
                </div>
              </div>
              <a href="${model.modelUrl}" target="_blank" class="view-button">
                View On Printables.com
              </a>
            </div>
          </div>
        </div>
      `;
    }
  };

})(Drupal, drupalSettings, once);