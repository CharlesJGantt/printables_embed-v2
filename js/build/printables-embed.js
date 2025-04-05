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
  
  // This MUST match the ID in your YAML file
  Drupal.CKEditor5.printables_embed_plugin = {
    plugins: [
      function printablesEmbed(Editor) {
        const Plugin = Editor.Plugin;
        const Command = Editor.Command;
        const ButtonView = Editor.ui.ButtonView;
        const Widget = Editor.Widget;
        const toWidget = Widget.toWidget;
        const toWidgetEditable = Widget.toWidgetEditable;
        
        // Create a custom printables command
        class InsertPrintablesCommand extends Command {
          execute() {
            const editor = this.editor;
            const url = prompt('Enter Printables URL:');
            
            if (url && url.includes('printables.com')) {
              const match = url.match(/(model|embed)\/(\d+)/);
              const modelId = match ? match[2] : null;
              
              if (modelId) {
                // Insert the element
                editor.model.change(writer => {
                  const printablesEmbed = writer.createElement('printablesEmbed', {
                    'data-printables-url': url,
                    'data-printables-id': modelId
                  });
                  
                  editor.model.insertContent(printablesEmbed);
                });
              } else {
                alert('Invalid Printables URL. Please use a URL like https://www.printables.com/model/12345');
              }
            }
          }
        }
        
        // Create our plugin
        class PrintablesEmbed extends Plugin {
          static get pluginName() {
            return 'PrintablesEmbed';
          }
          
          init() {
            const editor = this.editor;
            
            // Register the command
            editor.commands.add('insertPrintables', new InsertPrintablesCommand(editor));
            
            // Add a button to the toolbar
            editor.ui.componentFactory.add('printablesEmbed', locale => {
              const button = new ButtonView(locale);
              
              button.set({
                label: Drupal.t('Printables'),
                icon: '<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2C14.4183 2 18 5.58172 18 10C18 14.4183 14.4183 18 10 18C5.58172 18 2 14.4183 2 10C2 5.58172 5.58172 2 10 2ZM10 4C6.68629 4 4 6.68629 4 10C4 13.3137 6.68629 16 10 16C13.3137 16 16 13.3137 16 10C16 6.68629 13.3137 4 10 4ZM10 6C12.2091 6 14 7.79086 14 10C14 12.2091 12.2091 14 10 14C7.79086 14 6 12.2091 6 10C6 7.79086 7.79086 6 10 6Z" fill="#FA6831"/></svg>',
                tooltip: true
              });
              
              // Execute the command when the button is clicked
              button.on('execute', () => {
                editor.execute('insertPrintables');
              });
              
              return button;
            });
            
            // Define schema for the printables embed element
            editor.model.schema.register('printablesEmbed', {
              inheritAllFrom: '$blockObject',
              allowAttributes: ['data-printables-url', 'data-printables-id']
            });
            
            // Define conversion for the printables embed element - from data view to model
            editor.conversion.for('upcast').elementToElement({
              view: {
                name: 'div',
                classes: ['printables-embed']
              },
              model: (viewElement, { writer }) => {
                const url = viewElement.getAttribute('data-printables-url');
                const id = viewElement.getAttribute('data-printables-id');
                
                return writer.createElement('printablesEmbed', {
                  'data-printables-url': url,
                  'data-printables-id': id
                });
              }
            });
            
            // Define conversion for the printables embed element - from model to data view
            editor.conversion.for('dataDowncast').elementToElement({
              model: 'printablesEmbed',
              view: (modelElement, { writer }) => {
                const url = modelElement.getAttribute('data-printables-url');
                const id = modelElement.getAttribute('data-printables-id');
                
                const viewElement = writer.createContainerElement('div', {
                  class: 'printables-embed',
                  'data-printables-url': url,
                  'data-printables-id': id
                });
                
                writer.insert(
                  writer.createPositionAt(viewElement, 0),
                  writer.createText(`Printables Embed: ${url}`)
                );
                
                return viewElement;
              }
            });
            
            // Define conversion for the printables embed element - from model to editing view
            editor.conversion.for('editingDowncast').elementToElement({
              model: 'printablesEmbed',
              view: (modelElement, { writer }) => {
                const url = modelElement.getAttribute('data-printables-url');
                const id = modelElement.getAttribute('data-printables-id');
                
                // Create a view container
                const container = writer.createContainerElement('div', {
                  class: 'printables-embed-editor'
                });
                
                // Add a label for better UX in the editor
                const label = writer.createContainerElement('div', {
                  class: 'printables-embed-label'
                });
                
                // Add the URL text to the label
                writer.insert(
                  writer.createPositionAt(label, 0),
                  writer.createText(`Printables Embed: ${url}`)
                );
                
                // Add the label to the container
                writer.insert(writer.createPositionAt(container, 0), label);
                
                // Return the widget
                return toWidget(container, writer, {
                  label: 'Printables embed'
                });
              }
            });
          }
        }
        
        // Return the plugin class
        return {
          PrintablesEmbed
        };
      }
    ]
  };
})(Drupal);