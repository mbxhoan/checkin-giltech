import CKEDITOR from 'ckeditor';

const { ClassicEditor, SourceEditing } = CKEDITOR;

ClassicEditor
  .create(document.querySelector('#description'), {
    // licenseKey: '<YOUR_LICENSE_KEY>',
    plugins: [SourceEditing, /* ... */],
    toolbar: {
      items: [
        'sourceEditing',
        'undo', 'redo',
        '|', 'heading',
        '|', 'fontfamily', 'fontsize', 'fontColor', 'fontBackgroundColor',
        '|', 'bold', 'italic', 'strikethrough', 'subscript', 'superscript', 'code',
        '-', // break point
        '|', 'alignment',
        'link', 'uploadImage', 'blockQuote', 'codeBlock',
        '|', 'bulletedList', 'numberedList', 'todoList', 'outdent', 'indent'
      ],
      menuBar: {
        isVisible: true
      },
      shouldNotGroupWhenFull: true
    }
  })
  .then( /* ... */)
  .catch( /* ... */);
