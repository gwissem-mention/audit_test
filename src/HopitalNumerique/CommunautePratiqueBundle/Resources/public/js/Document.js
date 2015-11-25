/**
 * Classe gérant les documents de la communauté de pratique.
 */
var CommunautePratique_Document = function() {};


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
