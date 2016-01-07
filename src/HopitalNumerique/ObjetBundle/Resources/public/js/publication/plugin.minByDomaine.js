// nécessite un lien (id getObjets) avec pour href, l'url du formulaire
tinymce.PluginManager.add('publicationDomaine', function(editor, url) {

    // Add a button that opens a window
    editor.addButton('publicationDomaine', {
        text: 'Production ANAP',
        icon: false,
        onclick: function() {
            $.fancybox.open( Routing.generate('hopitalnumerique_objet_objet_getObjets_by_domaine'), {
                type     : "ajax",
                autoSize : false,
                width    : 800,
                height   : 300,
                ajax     : {
                    data : {
                        texte : editor.selection.getContent({format: 'text'})
                    },
                    type : "POST"
                },
                beforeClose : function(){
                    if( $('#choix').val() == "submit" ){
                        var publication = $('#publication').val();
                        if( publication != "" ){
                            var texte = $('#texte').val();
                            var cible = $('#cible').val();
                            editor.insertContent('[' + publication + ';' + texte + ';' + cible + ']');
                        }
                    }
                }
            });
        }
    });
});
