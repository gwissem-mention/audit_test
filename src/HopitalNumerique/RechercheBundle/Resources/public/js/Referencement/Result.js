/**
 * Affiche les résultats.
 */
Hn_RechercheBundle_Referencement.displayResults = function()
{
    $('#results-count').html('');
    var chosenReferenceIds = Hn_RechercheBundle_Referencement.getChosenReferenceIds();

    var ajaxOptions = {
        'references': chosenReferenceIds
    };
    if (Hn_RechercheBundle_Referencement_Filter_Category.hasFilter()) {
        var entityTypeIds = Hn_RechercheBundle_Referencement_Filter_Category.getEntityTypeIds();
        var publicationCategoryIds = Hn_RechercheBundle_Referencement_Filter_Category.getPublicationCategoryIds();

        ajaxOptions['entityTypeIds'] = entityTypeIds;
        if (publicationCategoryIds.length > 0) { // Si un filtre concernent une catégorie de publication, on récupère les types Objet + Contenu
            ajaxOptions['publicationCategoryIds'] = publicationCategoryIds;
        }
    }

    if (chosenReferenceIds.length > 0) {
        $.ajax({
            url: Routing.generate('hopitalnumerique_recherche_referencement_jsonentitiesbyreferences'),
            method: 'post',
            type: 'json',
            data: ajaxOptions,
            success: function(data) {
                var totalCount = 0;

                for (var group in data) {
                    if ($('#results-' + group + '-bloc').size() > 0) {
                        $('#results-' + group + '-bloc').css({ display: (data[group].length > 0 ? 'block' : 'none') });
                        if (data[group].length > 0) {
                            totalCount += data[group].length;

                            var index = 0;
                            var otherResultsHtml = '';

                            for (var i in data[group]) {
                                index++;
                                otherResultsHtml += '<div class="result" data-index="' + index + '" data-initialized="false" data-visible="false" data-entity-type="' + data[group][i].entityType + '" data-entity-id="' + data[group][i].entityId + '" data-pertinence-niveau="' + data[group][i].pertinenceNiveau + '"></div>';
                            }

                            $('#results-' + group).html(otherResultsHtml);
                            $('#results-' + group + '-count').html(data[group].length);
                            Hn_RechercheBundle_Referencement.displayMoreResults(group);
                            $('#results-' + group + '-bloc').show('fast');
                        }
                    }
                }

                if (totalCount > 0) {
                    $('#filtres-info').css({ display: 'none' });
                    $('#results-count').html('(' + totalCount + ' résultat' + (totalCount > 1 ? 's' : '') + ')');
                }
            }
        });
    } else {
        $('.results-bloc > div').slideUp('fast');
        $('#filtres-info').slideDown('fast');
    }

    Hn_RechercheBundle_Referencement.processFilterButtonsActivating();
    Hn_RechercheBundle_Referencement.displayDomaineResults();
};

/**
 * Affiche plus de résultats.
 *
 * @param string resultsGroup Groupe des résultats
 */
Hn_RechercheBundle_Referencement.displayMoreResults = function(resultsGroup)
{
    var entitiesContainers = $('#results-' + resultsGroup + ' [data-visible="false"]');
    var entitiesByType = {};

    $(entitiesContainers).each(function (i, entityContainer) {
        var entityType = $(entityContainer).attr('data-entity-type');
        var entityId = $(entityContainer).attr('data-entity-id');
        var pertinenceNiveau = $(entityContainer).attr('data-pertinence-niveau');

        if (undefined == entitiesByType[entityType]) {
            entitiesByType[entityType] = {};
        }
        entitiesByType[entityType][entityId] = {
            pertinenceNiveau: pertinenceNiveau
        };

        if (i == Hn_RechercheBundle_Referencement.RESULTS_RANGE - 1) {
            return false;
        }
    });

    $.ajax({
        url: Routing.generate('hopitalnumerique_recherche_referencement_jsonentities'),
        method: 'POST',
        data: {
            entitiesByType: entitiesByType
        },
        dataType: 'json',
        success: function (entitiesByType) {
            for (var entityType in entitiesByType) {
                for (var entityId in entitiesByType[entityType]) {
                    var entityContainer = $('.results-bloc [data-entity-type="' + entityType + '"][data-entity-id="' + entityId + '"]');
                    $(entityContainer).html(entitiesByType[entityType][entityId]['viewHtml']);
                    Hn_RechercheBundle_Referencement.displayEntity(resultsGroup, entityType, entityId);
                }
            }
        }
    });
};

/**
 * Affiche moins de résultats.
 *
 * @param string resultsGroup Groupe des résultats
 */
Hn_RechercheBundle_Referencement.displayLessResults = function(resultsGroup)
{
    var entitiesContainers = $('#results-' + resultsGroup + ' [data-visible="true"]').toArray().reverse();

    $(entitiesContainers).each(function (i, entityContainer) {
        $(entityContainer).attr('data-visible', 'false');
        $(entityContainer).slideUp('fast');

        if (i == Hn_RechercheBundle_Referencement.RESULTS_RANGE - 1) {
            return false;
        }
    });

    Hn_RechercheBundle_Referencement.processResultButtonsActivating(resultsGroup);
};

/**
 * Active ou pas les boutons Plus/moins de résultats.
 *
 * @param string resultsGroup Groupe des résultats
 */
Hn_RechercheBundle_Referencement.processResultButtonsActivating = function(resultsGroup)
{
    var lessResultsPossible = ($('#results-' + resultsGroup + ' [data-visible="true"]').size() > Hn_RechercheBundle_Referencement.RESULTS_RANGE);
    var moreResultsPossible = ($('#results-' + resultsGroup + ' [data-visible="false"]').size() > 0);

    $('#results-' + resultsGroup + '-less-button').prop('disabled', !lessResultsPossible);
    $('#results-' + resultsGroup + '-more-button').prop('disabled', !moreResultsPossible);
};

/**
 * Remplit le contenu de l'entité (sauf si déjà initialisé) et l'affiche.
 *
 * @param string resultsGroup Groupe des résultats
 * @param int entityType Type d'entité
 * @param int entityiD   ID de l'entité
 */
Hn_RechercheBundle_Referencement.displayEntity = function(resultsGroup, entityType, entityId)
{
    var entityContainer = $('.results-bloc [data-entity-type="' + entityType + '"][data-entity-id="' + entityId + '"]');

    $(entityContainer).attr('data-initialized', 'true');
    $(entityContainer).attr('data-visible', 'true');
    $(entityContainer).slideDown('fast');
    Hn_RechercheBundle_Referencement.processResultButtonsActivating(resultsGroup);
};
