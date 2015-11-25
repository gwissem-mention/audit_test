/**
 * Classe gérant les documents de la communauté de pratique.
 */
var CommunautePratique_Document = function() {};


/**
 * @var array<string, string> Icones des documents par extension de fichier
 */
CommunautePratique_Document.ICONES_BY_EXTENSION = {};


$(document).ready(function() {
    CommunautePratique_Document.init();
});


/**
 * Initialisation.
 */
CommunautePratique_Document.init = function() {
    //CommunautePratique_Document.initEnvoi();
};

/**
 * Initialise jQuery File Upload.
 * 
 * @param integer groupeId ID du groupe des formulaire.
 */
CommunautePratique_Document.initFormulaireEnvoi = function(groupeId)
{
    $('#fileupload').fileupload({
        url: Routing.generate('hopitalnumerique_communautepratique_document_upload', { groupe:groupeId }),
        singleFileUploads: false,
        maxFileSize: 5 * 1024 * 1024, // 5 Mo
        added: function(e, data) {
            
            //<-- Affichage de l'icône personnalisé si existant
            $.each($('.template-upload'), function(i, element) {
                var nomDocument = $(element).find('.name').html().trim();

                if (nomDocument.indexOf('.') > -1)
                {
                    var extension = nomDocument.substr(nomDocument.lastIndexOf('.') + 1);

                    if (CommunautePratique_Document.ICONES_BY_EXTENSION[extension] !== undefined)
                    {
                        $(element).find('.icone').html(CommunautePratique_Document.ICONES_BY_EXTENSION[extension]);
                    }
                }
            });
            //-->

            if ('none' == $('.bloc-envoi-documents').css('display'))
            {
                $('.bloc-envoi-documents').show('blind', {}, 100);
            }
        },
        done: function () {
            window.location = Routing.generate('hopitalnumerique_communautepratique_document_listbygroupe', { groupe:groupeId });
        }
    });
};

/**
 * Initialise jQuery File Upload.
 * 
 * @param integer groupeId ID du groupe des formulaire.
 */
CommunautePratique_Document.delete = function(documentId)
{
    if (confirm('Confirmez-vous la suppression définitive de ce document ?'))
    {
        $.ajax({
            url: Routing.generate('hopitalnumerique_communautepratique_document_delete', { document:documentId }),
            method: 'post',
            dataType: 'json'
        }).done(function(data) {
            if (data.success)
            {
                $('[data-communaute-pratique-document-id=' + documentId + ']').hide('drop');
            }
            else
            {
                alert('Le document n\'a pu être supprimé.');
            }
        });
    }
};
