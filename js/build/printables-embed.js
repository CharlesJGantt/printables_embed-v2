/**
 * @file
 * Printables Embed plugin for CKEditor 5.
 */
(function (Drupal) {
  'use strict';

  /**
   * @type {Drupal.CKEditor5~PluginDefinition}
   */
  Drupal.CKEditor5 = Drupal.CKEditor5 || {};
  
  // This MUST use the same ID as your YAML file
  Drupal.CKEditor5.printables_embed_plugin = {
    plugins: [
      function printablesEmbed(editor) {
        // Register the plugin with CKEditor 5
        // Create a simple plugin class
        class PrintablesEmbed {
          constructor(editor) {
            this.editor = editor;
          }

          static get pluginName() {
            return 'PrintablesEmbed';
          }

          init() {
            const editor = this.editor;
            
            // Register a command for inserting printables embeds
            editor.commands.add('insertPrintablesEmbed', {
              execute: () => {
                const url = prompt('Enter Printables URL:');
                
                if (url && url.includes('printables.com')) {
                  const match = url.match(/(model|embed)\/(\d+)/);
                  const modelId = match ? match[2] : null;
                  
                  if (modelId) {
                    // Insert as plain HTML
                    const htmlContent = `<div class="printables-embed" data-printables-url="${url}" data-printables-id="${modelId}">Printables Embed: ${url}</div>`;
                    const viewFragment = editor.data.processor.toView(htmlContent);
                    const modelFragment = editor.data.toModel(viewFragment);
                    editor.model.insertContent(modelFragment);
                  } else {
                    alert('Invalid Printables URL. Please use a URL like https://www.printables.com/model/12345');
                  }
                }
              }
            });
            
            // Register the toolbar button
            editor.ui.componentFactory.add('printablesEmbed', locale => {
              // Create a button view
              const buttonView = editor.ui.componentFactory.create('button');
              
              // Set button properties
              buttonView.set({
                label: editor.t('Printables'),
                icon: '<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2C14.4183 2 18 5.58172 18 10C18 14.4183 14.4183 18 10 18C5.58172 18 2 14.4183 2 10C2 5.58172 5.58172 2 10 2ZM10 4C6.68629 4 4 6.68629 4 10C4 13.3137 6.68629 16 10 16C13.3137 16 16 13.3137 16 10C16 6.68629 13.3137 4 10 4ZM10 6C12.2091 6 14 7.79086 14 10C14 12.2091 12.2091 14 10 14C7.79086 14 6 12.2091 6 10C6 7.79086 7.79086 6 10 6Z" fill="#FA6831"/></svg>',
                tooltip: true
              });
              
              // Execute the command when the button is clicked
              buttonView.on('execute', () => {
                editor.execute('insertPrintablesEmbed');
              });
              
              return buttonView;
            });
          }
        }
        
        // Add the plugin to the editor
        editor.plugins.add('PrintablesEmbed', PrintablesEmbed);
        
        return editor;
      }
    ]
  };
})(Drupal);