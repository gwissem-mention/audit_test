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
    Hn_Reference_Form_Reference.processInRechercheDisplaying();
};

/**
 * Initialisation des événements.
 */
Hn_Reference_Form_Reference.initEvents = function()
{
    $('#hopitalnumerique_reference_reference_reference').click(function() {
        Hn_Reference_Form_Reference.processInRechercheDisplaying();
    });
};

/**
 * Affiche ou pas la case "Présente dans la recherche ?".
 */
Hn_Reference_Form_Reference.processInRechercheDisplaying = function()
{
    var isReference = $('#hopitalnumerique_reference_reference_reference').is(':checked');

    if (!isReference) {
        $('#hopitalnumerique_reference_reference_inRecherche').prop('checked', false);
    }
    $('#reference-in-recherche-container').css({ display:(isReference ? 'block' : 'none') });
};
