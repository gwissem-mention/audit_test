/**
 * Gestion de la popin "Mon contexte".
 */
var Hn_RechercheBundle_Referencement_Filter_Contexte = function() {};


/**
 * Valide les choix de l'utilisateur.
 */
Hn_RechercheBundle_Referencement_Filter_Contexte.valid = function()
{
    var saveUserContext = ($('input[name="contexte-valid"]').is(':checked'));

    $('#contexte-modal [data-chosen="true"]').attr('data-chosen', false);

    $('#contexte-modal input:checked').each(function(i, input) {
        var referenceId = Hn_RechercheBundle_Referencement.getReferenceIdByElement(input);
        $('#contexte-modal [data-reference="' + referenceId + '"]').attr('data-chosen', true);
    });

    Hn_RechercheBundle_Referencement.initReferenceFilters();
    $('#contexte-modal').modal('hide');

    if (saveUserContext) {
        Hn_RechercheBundle_Referencement_Filter_Contexte.saveUser();
    }
};

/**
 * Enregistre le contexte sur l'utilisateur.
 */
Hn_RechercheBundle_Referencement_Filter_Contexte.saveUser = function()
{
    var contexteReferenceIds = [];
    var contextechoosenElements = $('#contexte-modal [data-chosen="true"]');

    $(contextechoosenElements).each(function (i, element) {
        contexteReferenceIds.push(parseInt($(element).attr('data-reference')));
    });

    $.ajax({
        url: Routing.generate('hopitalnumerique_account_contexte_save'),
        method: 'POST',
        data: {
            'referenceIds': contexteReferenceIds
        },
        dataType: 'json'/*,
        success: function (data) {
            if (data.save) {
                apprise('Votre compte a été modifié.');
            }
        }*/
    });
};
