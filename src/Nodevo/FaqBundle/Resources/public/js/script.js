$(document).ready(function() {
    tinymce.PluginManager.load('code', '/bundles/nodevoadmin/plugins/tinymce/plugins/code/plugin.min.js');
    tinymce.PluginManager.load('link', '/bundles/nodevoadmin/plugins/tinymce/plugins/link/plugin.min.js');

    tinyMCE.init({
        selector     : "textarea",
        theme        : "modern",
        theme_url    : '/bundles/nodevoadmin/plugins/tinymce/themes/modern/theme.min.js',
        skin_url     : '/bundles/nodevoadmin/plugins/tinymce/skins/lightgray',
        plugins      : 'code link',
        height       : 210,
        menubar      : false,
        toolbar1     : "code | undo redo cut copy paste | pagebreak | link | insertfile ",
        toolbar2     : "styleselect | bold italic underline strikethrough subscript superscript blockquote | forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent",
        style_formats: [
            {title: 'Titres', items: [
                {title: 'Titre 2', block: 'h2'},
                {title: 'Titre 3', block: 'h3'},
                {title: 'Titre 4', block: 'h4'},
                {title: 'Titre 5', block: 'h5'},
                {title: 'Titre 6', block: 'h6'}
            ]},
            {title: 'Blocs', items: [
                {title: 'Paragraphe', block: 'p'}
            ]}
        ],
        relative_urls : false
    });
});