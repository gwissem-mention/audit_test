/**
 * Gestion de la popin de détails de la requête.
 */
var Hn_RechercheBundle_Requete_PopinDetail = new function() {};

/**
 * Initialisation.
 */
Hn_RechercheBundle_Requete_PopinDetail.init = function()
{
    Hn_RechercheBundle_Requete_PopinDetail.displayChosenReferences();
};


/**
 * Affiche les références choisies.
 */
Hn_RechercheBundle_Requete_PopinDetail.displayChosenReferences = function()
{
    $('[data-chosen="true"]').each(function(i, reference) {
        Hn_RechercheBundle_Requete_PopinDetail.displayReferenceId(parseInt($(reference).attr('data-reference')));
    });
};

/**
 * Affiche une référence.
 *
 * @param integer referenceId ID de référence
 */
Hn_RechercheBundle_Requete_PopinDetail.displayReferenceId = function(referenceId)
{
    var referenceElement = $('[data-reference="' + referenceId + '"]');
    var referenceParentIdAttr = $(referenceElement).attr('data-reference-parent');

    $(referenceElement).css({ display:'block' });
    if ('' !== referenceParentIdAttr) {
        Hn_RechercheBundle_Requete_PopinDetail.displayReferenceId(parseInt(referenceParentIdAttr));
    }
};
