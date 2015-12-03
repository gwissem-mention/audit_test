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
    // Ordre lors du choix : added
    // Ordre à l'envoi :     submit started send sent done
    $('#fileupload').fileupload({
        url: Routing.generate('hopitalnumerique_communautepratique_document_upload', { groupe:groupeId }),
        singleFileUploads: false,
        //maxFileSize: 5 * 1024 * 1024, // 5 Mo
        // N'est appelé que si tous les fichiers sont valides (non appelée si par exemple un fichier est trop gros)
        // Même si un seul fichier n'est pas valide parmi d'autres, on ne peut pas envoyer les fichiers (dans ce cas, il faut rechoisir un fichier valide)
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
        submit: function() {
            $('.fileupload-progress').show('blind', {}, 100);
        },
        done: function () {
            window.location = Routing.generate('hopitalnumerique_communautepratique_document_listbygroupe', { groupe:groupeId });
        }
    });
    
    $('.fileupload-buttonbar button.cancel').click(function() {
        window.location = Routing.generate('hopitalnumerique_communautepratique_document_listbygroupe', { groupe:groupeId });
    });
};

/**
 * Supprime un document.
 *
 * @param integer documentId ID du document
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
            if (data.success) {
                $('[data-communaute-pratique-document-id=' + documentId + ']').parent().hide();
            } else {
                alert('Le document n\'a pu être supprimé.');
            }
        });
    }
};

/**
 * Filtre la liste des documents selon des extensions.
 *
 * @param string extensions Extensions (séparées par une virgule). Si NULL, aucun filtre
 */
CommunautePratique_Document.filtreByExtensions = function(extensions)
{
    if ('' != extensions) {
        $('[data-communaute-pratique-document-extension]').parent().css({ display: 'none' });
        var extensionsAutorisees = extensions.split(',');

        for (var i in extensionsAutorisees) {
            $('[data-communaute-pratique-document-extension=' + extensionsAutorisees[i] + ']').parent().css({ display: 'block' });
        }
    } else {
        $('[data-communaute-pratique-document-extension]').parent().css({ display: 'block' });
    }
};
