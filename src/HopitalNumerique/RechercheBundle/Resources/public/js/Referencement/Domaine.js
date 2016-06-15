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
        if (elementDomaineId.length > 0) {
            domaineIds.push(parseInt(elementDomaineId));
        }
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
    var chosenDomaineIds = [];
    var domaineResultsHtml = [];
    var choosenElements = [];

    $.each(Hn_RechercheBundle_Referencement.getChosenElements(), function (i, element) {
        var currentLength = chosenDomaineIds.length;
        $.each(Hn_RechercheBundle_Referencement.getDomaineIdsByElement(element), function (j, domaineId) {
            if ($.inArray(domaineId, chosenDomaineIds) === -1) {
                chosenDomaineIds.push(domaineId);

                if (Hn_DomaineBundle_Domaine.CURRENT_DOMAINE_ID !== domaineId) {
                    var referenceString = Hn_RechercheBundle_Referencement.getChosenReferenceIdsByDomaineId(domaineId).join('-');
                    var domaineLink =
                        Hn_DomaineBundle_Domaine.getUrlById(domaineId)
                        + Routing.generate(
                            'hopital_numerique_recherche_homepage_requete_generator',
                            {
                                refs: referenceString
                            },
                            false
                        );
                    domaineResultsHtml.push('<a href="' + domaineLink + '" target="_blank">' + Hn_DomaineBundle_Domaine.getNomById(domaineId) + '</a>');
                }
            }
        });
        if (chosenDomaineIds.length > currentLength) {
            choosenElements.push(element);
        }
    });

    if (domaineResultsHtml.length > 0) {
        $('#results-domaines .filters').html('"' +
            choosenElements.map(function(e) {
                 return $(e).data('libelle');
            }).join(', ')
            + '"'
        );
        $('#results-domaines .domaines').html(domaineResultsHtml.join(', '));
        $('#results-domaines').css({ display: 'block' });
    } else {
        $('#results-domaines').css({ display: 'none' });
    }
};
