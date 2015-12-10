$(document).ready(function() {
    tinymce.PluginManager.load('table', '/bundles/nodevotools/js/tinymce/plugins/table/plugin.min.js');
    tinymce.PluginManager.load('code', '/bundles/nodevotools/js/tinymce/plugins/code/plugin.min.js');
    tinymce.PluginManager.load('pagebreak', '/bundles/nodevotools/js/tinymce/plugins/pagebreak/plugin.min.js');
    tinymce.PluginManager.load('importcss', '/bundles/nodevotools/js/tinymce/plugins/importcss/plugin.min.js');
    tinymce.PluginManager.load('image', '/bundles/nodevotools/js/tinymce/plugins/image/plugin.min.js');
    tinymce.PluginManager.load('link', '/bundles/nodevotools/js/tinymce/plugins/link/plugin.min.js');
    tinymce.PluginManager.load('media', '/bundles/nodevotools/js/tinymce/plugins/media/plugin.min.js');
    tinymce.PluginManager.load('textcolor', '/bundles/nodevotools/js/plugins/text-color/plugin.min.js');
    tinymce.PluginManager.load('publication', '/bundles/hopitalnumeriqueobjet/js/publication/plugin.min.js');
    tinymce.PluginManager.load('outil', '/bundles/hopitalnumeriqueautodiag/js/outil/plugin.min.js');
    tinymce.PluginManager.load('questionnaire', '/bundles/hopitalnumeriquequestionnaire/js/Back_gestion/plugin.min.js');
    tinymce.PluginManager.load('rechercheAidee', '/bundles/hopitalnumeriquerecherche/js/rechercheAidee/plugin.min.js');
    NodevoGestionnaireMediaBundle_MoxieManager.initTinyMce();
    
    tinyMCE.init({
        entity_encoding : "raw",
        selector     : "textarea.tinyMce",
        theme        : "modern",
        theme_url    : '/bundles/nodevotools/js/tinymce/themes/modern/theme.min.js',
        skin_url     : '/bundles/nodevotools/js/tinymce/skins/lightgray',
        plugins      : 'moxiemanager image table code textcolor pagebreak importcss link publication outil questionnaire rechercheAidee media',
        height       : 210,
        menubar      : false,
        content_css  : '/bundles/nodevotools/css/wysiwyg.css',
        toolbar1     : "code | undo redo cut copy paste | pagebreak | link | publication | outil | questionnaire | rechercheAidee | insertfile image media ",
        toolbar2     : "styleselect | bold italic underline strikethrough subscript superscript blockquote | forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table ",
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
});
