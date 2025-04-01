// This is a placeholder for the built code
// In a production environment, you would use a build system like webpack
// For now, we'll simulate the built plugin code

(function(Drupal, CKEditor5) {
  class PrintablesEmbed {
    static get pluginName() {
      return 'PrintablesEmbed';
    }

    init() {
      const editor = this.editor;
      const t = editor.t;
      
      editor.ui.componentFactory.add('printablesEmbed', locale => {
        const buttonView = new editor.ui.ButtonView(locale);
        
        buttonView.set({
          label: t('Insert Printables'),
          tooltip: true,
          withText: false
        });
        
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

  // Register the plugin with CKEditor 5
  CKEditor5.builtins = CKEditor5.builtins || {};
  CKEditor5.builtins.printables_embed = {
    PrintablesEmbed: PrintablesEmbed
  };
})(Drupal, CKEditor5);