/**
 * Gestion de la recherche avancée (par référencement).
 */
var Hn_RechercheBundle_Referencement = function() {};

$(document).ready(function () {
    Hn_RechercheBundle_Referencement.init();
});

/**
 * Initialisation.
 */
Hn_RechercheBundle_Referencement.init = function()
{
    Hn_RechercheBundle_Referencement.initEvents();
};

/**
 * Initialisation des événements.
 */
Hn_RechercheBundle_Referencement.initEvents = function()
{
    $('a.reference').click(function() {
        var referenceId = $(this).attr('data-reference');

        Hn_RechercheBundle_Referencement.toggleReferenceDisplaying(referenceId);
    });
};

Hn_RechercheBundle_Referencement.toggleReferenceDisplaying = function(referenceId)
{
    var referenceChildrenList = $('ul[data-reference="' + referenceId + '"]');

    if ($(referenceChildrenList).size() > 0) {
        if ('none' === $(referenceChildrenList).css('display')) {
            $(referenceChildrenList).slideDown();
        } else {
            $(referenceChildrenList).slideUp();
        }
    }
};
