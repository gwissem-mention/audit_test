/**
 * Classe gérant le formulaire d'une référence.
 * 
 * @author Rémi Leclerc
 */
var Hn_Reference_Form_Reference = function() {};


$(document).ready(function() {
    Hn_Reference_Form_Reference.init();
});


/**
 * Initialisation.
 */
Hn_Reference_Form_Reference.init = function()
{
    Hn_Reference_Form_Reference.initEvents();
    Hn_Reference_Form_Reference.processInRechercheAndParentableDisplaying();
    Hn_Reference_Form_Reference.processDescriptionCourteObligatory();
};

/**
 * Initialisation des événements.
 */
Hn_Reference_Form_Reference.initEvents = function()
{
    $('#hopitalnumerique_reference_reference_reference').click(function() {
        Hn_Reference_Form_Reference.processInRechercheAndParentableDisplaying();
    });
    $('#hopitalnumerique_reference_reference_inGlossaire').click(function() {
        Hn_Reference_Form_Reference.processDescriptionCourteObligatory();
    });
};

/**
 * Affiche ou pas la case "Présente dans la recherche ?".
 */
Hn_Reference_Form_Reference.processInRechercheAndParentableDisplaying = function()
{
    var isReference = $('#hopitalnumerique_reference_reference_reference').is(':checked');

    if (!isReference) {
        $('#hopitalnumerique_reference_reference_inRecherche').prop('checked', false);
        $('#hopitalnumerique_reference_reference_parentable').prop('checked', false);
    }
    $('#reference-in-recherche-container').css({ display:(isReference ? 'block' : 'none') });
    $('#reference-parentable-container').css({ display:(isReference ? 'block' : 'none') });
};

/**
 * Rend la description courte obligatoire si le concept est présent dans le glossaire.
 */
Hn_Reference_Form_Reference.processDescriptionCourteObligatory = function()
{
    var isInGlossaire = $('#hopitalnumerique_reference_reference_inGlossaire').is(':checked');

    if (isInGlossaire) {
        $('#hopitalnumerique_reference_reference_descriptionCourte').addClass('validate[required]');
    } else {
        $('#hopitalnumerique_reference_reference_descriptionCourte').removeClass('validate[required]');
    }
};
