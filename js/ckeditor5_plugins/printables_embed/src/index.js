/**
 * @file
 * Printables Embed plugin for CKEditor 5.
 */

import { Plugin } from 'ckeditor5/src/core';
import { ButtonView } from 'ckeditor5/src/ui';

/**
 * The Printables Embed plugin.
 */
export default class PrintablesEmbed extends Plugin {
  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'PrintablesEmbed';
  }

  /**
   * @inheritdoc
   */
  init() {
    const editor = this.editor;
    const config = editor.config.get('printables_embed') || {};
    
    editor.ui.componentFactory.add('printablesEmbed', (locale) => {
      const buttonView = new ButtonView(locale);

      buttonView.set({
        label: config.buttonLabel || 'Insert Printables',
        tooltip: true,
        withText: false,
      });

      // Execute command when button is clicked
      buttonView.on('execute', () => {
        const url = prompt('Enter Printables URL:');
        
        if (url && url.includes('printables.com')) {
          const match = url.match(/(model|embed)\/(\d+)/);
          const modelId = match ? match[2] : null;
          
          if (modelId) {
            // Insert a placeholder element
            const html = `<div class="printables-embed" data-printables-url="${url}" data-printables-id="${modelId}">Printables Embed: ${url}</div>`;
            const viewFragment = editor.data.processor.toView(html);
            const modelFragment = editor.data.toModel(viewFragment);
            editor.model.insertContent(modelFragment);
          } else {
            alert('Invalid Printables URL. Please use a URL like https://www.printables.com/model/12345');
          }
        }
      });

      return buttonView;
    });
  }
}