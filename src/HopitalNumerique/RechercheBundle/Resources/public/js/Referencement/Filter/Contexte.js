/**
 * Gestion de la popin "Mon contexte".
 */
var Hn_RechercheBundle_Referencement_Filter_Contexte = function() {};

/**
 * Valide les choix de l'utilisateur.
 */
Hn_RechercheBundle_Referencement_Filter_Contexte.valid = function()
{
    $('#contexte-modal [data-chosen="true"]').attr('data-chosen', false);

    $('#contexte-modal input:checked').each(function(i, input) {
        var referenceId = Hn_RechercheBundle_Referencement.getReferenceIdByElement(input);
        $('#contexte-modal [data-reference="' + referenceId + '"]').attr('data-chosen', true);
    });

    Hn_RechercheBundle_Referencement.initReferenceFilters();
    $('#contexte-modal').modal('hide');
};
