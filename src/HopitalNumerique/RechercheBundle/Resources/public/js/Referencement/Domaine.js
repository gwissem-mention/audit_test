/**
 * Retourne les ID des domaines des références choisies.
 */
Hn_RechercheBundle_Referencement.getChosenDomaineIds = function()
{
    var chosenDomaineIds = [];

    $.each(Hn_RechercheBundle_Referencement.getChosenElements(), function (i, element) {
        $.each(Hn_RechercheBundle_Referencement.getDomaineIdsByElement(element), function (j, domaineId) {
            if ($.inArray(domaineId, chosenDomaineIds) === -1) {
                chosenDomaineIds.push(domaineId);
            }
        })
    });

    return chosenDomaineIds;
};

/**
 * Retourne les ID des domaines d'un élément d'une référence.
 *
 * @return Array<integer> IDs des domaines
 */
Hn_RechercheBundle_Referencement.getDomaineIdsByElement = function(element)
{
    var domaineIds = [];

    $.each($(element).attr('data-domaines').split(','), function (i, elementDomaineId) {
        domaineIds.push(parseInt(elementDomaineId));
    });

    return domaineIds;
};

/**
 * Retourne les références choisies pour un domaine en particulier.
 *
 * @return integer ID du domaine
 */
Hn_RechercheBundle_Referencement.getChosenReferenceIdsByDomaineId = function(domaineId)
{
    var chosenReferenceIds = [];
    var chosenReferences = Hn_RechercheBundle_Referencement.getChosenElements();

    $(chosenReferences).each(function(i, referenceElement) {
        if ($.inArray(domaineId, Hn_RechercheBundle_Referencement.getDomaineIdsByElement(referenceElement)) > -1) {
            chosenReferenceIds.push($(referenceElement).attr('data-reference'));
        }
    });

    return chosenReferenceIds;
};

/**
 * Affiche les résultats des autres domaines.
 */
Hn_RechercheBundle_Referencement.displayDomaineResults = function()
{
    var domaineResultsHtml = '';

    $.each(Hn_RechercheBundle_Referencement.getChosenDomaineIds(), function (i, domaineId) {
        if (Hn_DomaineBundle_Domaine.CURRENT_DOMAINE_ID !== domaineId) {
            domaineResultsHtml += '<li>' + Hn_DomaineBundle_Domaine.getNomById(domaineId) + '</li>';
        }
    });

    if ('' !== domaineResultsHtml) {
        $('#results-domaines ul').html(domaineResultsHtml);
        $('#results-domaines').css({ display: 'block' });
    } else {
        $('#results-domaines').css({ display: 'none' });
    }
};
