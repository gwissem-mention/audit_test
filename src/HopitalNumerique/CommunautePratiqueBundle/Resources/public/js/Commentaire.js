/**
 * Classe gérant les commentaires de la communauté de pratique.
 */
var CommunautePratique_Commentaire = function() {};

/**
 * Initialise TinyMce.
 *
 * @param integer groupeId ID du groupe
 */
CommunautePratique_Commentaire.initTinyMce = function(groupeId)
{
    CommunautePratique_Commentaire.initTinyMcePluginDocuments(groupeId);
    tinymce.PluginManager.load('publicationDomaine', '/bundles/hopitalnumeriqueobjet/js/publication/plugin.minByDomaine.js');
};
/**
 * Initialise le plugin TinyMce permetant de créer un lien vers un document.
 *
 * @param integer groupeId ID du groupe
 */
CommunautePratique_Commentaire.initTinyMcePluginDocuments = function(groupeId)
{
    tinymce.PluginManager.add('communautePratiqueDocument', function(editor, url) {
        editor.addButton('communautePratiqueDocument', {
            text: 'Publier un élément de mon porte document',
            icon: false,
            onclick: function() {
                $.fancybox.open(Routing.generate('hopitalnumerique_communautepratique_tinymce_documents', { groupe:groupeId }), {
                    type     : 'ajax',
                    autoSize : false,
                    width    : 800,
                    height   : 250,
                    ajax     : {
                        data : {
                            texteSelectionne: editor.selection.getContent({format: 'text'})
                        },
                        type : "POST"
                    },
                    beforeClose : function() {
                        if ( $('#choix').val() == 'submit') {
                            var documentId = $('#document').val();
                            var texte = $('#texte').val();
                            if (documentId != '' && texte != '') {
                                editor.insertContent('<a class="fa fa-file-o" href="' + Routing.generate('hopitalnumerique_communautepratique_document_download', { document:documentId }) + '"> ' + texte + ' </a>');
                            }
                        }
                    }
                });
            }
        });
    });
};

/**
 * Appelle TinyMce pour les champs de formulaire.
 *
 * @param Element element Sélecteur
 */
CommunautePratique_Commentaire.callTinyMce = function(element)
{
    tinyMCE.init({
        entity_encoding : 'raw',
        selector     : element,
        theme        : "modern",
        theme_url    : '/bundles/nodevotools/js/tinymce/themes/modern/theme.min.js',
        skin_url     : '/bundles/nodevotools/js/tinymce/skins/lightgray',
        plugins      : 'paste link communautePratiqueDocument publicationDomaine',
        height       : 120,
        menubar      : false,
        content_css  : '/bundles/nodevotools/css/wysiwyg.css',
        toolbar1     : 'bold | underline | italic | link | communautePratiqueDocument | publicationDomaine',
        relative_urls: false,
        statusbar    : false,
        paste_as_text: true
    });
};

/**
 * Permet d'éditer le commentaire.
 *
 * @param integer commentaireId ID du commentaire
 */
CommunautePratique_Commentaire.edit = function(commentaireId)
{
    $.ajax({
        url: Routing.generate('hopitalnumerique_communautepratique_commentaire_edit', { commentaire:commentaireId }),
        method: 'post',
        success: function(html) {
            $('[data-communaute-pratique-commentaire-id=' + commentaireId + '] .message').html(html);
        }
    });
};

/**
 * Supprime le commentaire.
 *
 * @param integer commentaireId ID du commentaire
 */
CommunautePratique_Commentaire.delete = function(commentaireId)
{
    if (confirm('Confirmez-vous la suppression définitive de ce commentaire ?'))
    {
        $.ajax({
            url: Routing.generate('hopitalnumerique_communautepratique_commentaire_delete', { commentaire:commentaireId }),
            method: 'post',
            dataType: 'json'
        }).done(function(data) {
            if (data.success) {
                $('[data-communaute-pratique-commentaire-id=' + commentaireId + ']').slideUp();
            } else {
                alert('Le commentaire n\'a pu être supprimé.');
            }
        });
    }
};
