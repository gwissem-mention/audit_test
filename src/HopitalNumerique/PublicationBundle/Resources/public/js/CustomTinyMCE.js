tinyMCE.init({
    entity_encoding : "raw",
    selector     : "textarea.tinyMce",
    theme        : "modern",
    theme_url    : '/bundles/nodevotools/js/tinymce/themes/modern/theme.min.js',
    skin_url     : '/bundles/nodevotools/js/tinymce/skins/lightgray',
    plugins      : 'moxiemanager image table code textcolor pagebreak importcss link publication outil questionnaire rechercheAidee rechercheTexte media',
    height       : 210,
    menubar      : false,
    content_css  : '/bundles/nodevotools/css/wysiwyg.css',
    toolbar1     : "styleselect | bold italic underline strikethrough subscript superscript blockquote | forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table | link",
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
        ]},
        {title: 'Tableaux', items: [
            {title: 'Tableau simple', selector: 'table', classes: 'table table-striped table-bordered table-hover'}
        ]}
    ],
    importcss_append: true,
    importcss_groups: [
        {title: 'Styles personnalisés'}
    ],
    relative_urls:false
});