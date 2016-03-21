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
    Hn_Reference_Form_Reference.processReferenceChecking();
    Hn_Reference_Form_Reference.processInGlossaireChecking();
};

/**
 * Initialisation des événements.
 */
Hn_Reference_Form_Reference.initEvents = function()
{
    $('#hopitalnumerique_reference_reference_reference').click(function() {
        Hn_Reference_Form_Reference.processReferenceChecking();
    });
    $('#hopitalnumerique_reference_reference_inGlossaire').click(function() {
        Hn_Reference_Form_Reference.processInGlossaireChecking();
    });
};

/**
 * Affiche ou pas la case "Présente dans la recherche ?" et "Parentable ?" si "Est une référence ?" est cochée.
 */
Hn_Reference_Form_Reference.processReferenceChecking = function()
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
 * Rend la description courte obligatoire et affiche les champs du glossaire si le concept est présent dans le glossaire.
 */
Hn_Reference_Form_Reference.processInGlossaireChecking = function()
{
    var isInGlossaire = $('#hopitalnumerique_reference_reference_inGlossaire').is(':checked');

    $('#reference-glossaire-subcontainer').css({ display:(isInGlossaire ? 'block' : 'none') });
    if (isInGlossaire) {
        $('#hopitalnumerique_reference_reference_descriptionCourte').addClass('validate[required]');
    } else {
        $('#hopitalnumerique_reference_reference_descriptionCourte').removeClass('validate[required]');
    }
};
