(function (Drupal, CKEditor5) {
  'use strict';

  Drupal.CKEditor5.addPlugin('printables_embed', {
    init: function(editor) {
      editor.ui.componentFactory.add('printables_embed', locale => {
        const button = new editor.ui.ButtonView(locale);
        
        button.set({
          label: editor.config.get('printables_embed.buttonLabel'),
          tooltip: true,
          icon: '<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2C14.4183 2 18 5.58172 18 10C18 14.4183 14.4183 18 10 18C5.58172 18 2 14.4183 2 10C2 5.58172 5.58172 2 10 2ZM10 4C6.68629 4 4 6.68629 4 10C4 13.3137 6.68629 16 10 16C13.3137 16 16 13.3137 16 10C16 6.68629 13.3137 4 10 4ZM10 6C12.2091 6 14 7.79086 14 10C14 12.2091 12.2091 14 10 14C7.79086 14 6 12.2091 6 10C6 7.79086 7.79086 6 10 6Z" fill="#FA6831"/></svg>'
        });

        button.on('execute', () => {
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

        return button;
      });
    }
  });
})(Drupal, CKEditor5);