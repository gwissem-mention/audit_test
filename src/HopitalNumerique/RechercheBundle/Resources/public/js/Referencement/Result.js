/**
 * Affiche les résultats.
 */
Hn_RechercheBundle_Referencement.displayResults = function()
{
    $('#results-count').html('');
    var chosenGroupedReferenceIds = Hn_RechercheBundle_Referencement.getChosenGroupedReferenceIds();

    var ajaxOptions = {
        'references': chosenGroupedReferenceIds
    };
    if (Hn_RechercheBundle_Referencement_Filter_Category.hasFilter()) {
        var entityTypeIds = Hn_RechercheBundle_Referencement_Filter_Category.getEntityTypeIds();
        var publicationCategoryIds = Hn_RechercheBundle_Referencement_Filter_Category.getPublicationCategoryIds();

        ajaxOptions['entityTypeIds'] = entityTypeIds;
        if (publicationCategoryIds.length > 0) { // Si un filtre concernent une catégorie de publication, on récupère les types Objet + Contenu
            ajaxOptions['publicationCategoryIds'] = publicationCategoryIds;
        }
    }
    if (Hn_RechercheBundle_Referencement_Filter_Exalead.hasSearch() && Hn_RechercheBundle_Referencement_Filter_Exalead.processSearchValidating()) {
        ajaxOptions['exaleadSearch'] = Hn_RechercheBundle_Referencement_Filter_Exalead.getSearchedText();
    }

    if (chosenGroupedReferenceIds.length > 0) {
        $('#filtres-info').slideUp('fast');
    }

    $.ajax({
        url: Routing.generate('hopitalnumerique_recherche_referencement_jsonentitiesbyreferences'),
        method: 'post',
        type: 'json',
        data: ajaxOptions,
        success: function(data) {
            var totalCount = 0;

            $.each(data.results, function(group, groupEntities) {
                if ($('#results-' + group + '-bloc').size() > 0) {
                    $('#results-' + group + '-bloc').css({ display: (groupEntities.length > 0 ? 'block' : 'none') });
                    if (groupEntities.length > 0) {
                        totalCount += groupEntities.length;

                        var index = 0;
                        var otherResultsHtml = '';

                        for (var i in groupEntities) {
                            index++;
                            var isInitialized = (groupEntities[i].description != undefined ? 'true' : 'false');
                            var content = ('true' === isInitialized ? Hn_RechercheBundle_Referencement.getEntityBlocHtml(groupEntities[i]) : '');
                            otherResultsHtml += '<div class="result" data-index="' + index + '" data-initialized="' + isInitialized + '" data-visible="false" data-entity-type="' + groupEntities[i].entityType + '" data-entity-id="' + groupEntities[i].entityId + '" data-pertinence-niveau="' + groupEntities[i].pertinenceNiveau + '"' + (groupEntities[i].categoryIds.length > 0 ? ' data-categories="' + groupEntities[i].categoryIds.join(' ') + '"' : '') + '>' + content + '</div>';
                        }

                        $('#results-' + group).html(otherResultsHtml);
                        $('#results-' + group + '-count').html(groupEntities.length);
                        Hn_RechercheBundle_Referencement.displayMoreResults(group);
                        $('#results-' + group + '-bloc').show('fast');
                    }
                }
            });

            if (totalCount > 0) {
                $('#no-result-bloc').slideUp('fast');
                $('#results-count').html('(' + totalCount + ' résultat' + (totalCount > 1 ? 's' : '') + ')');
                Hn_RechercheBundle_Referencement_Filter_Category.displayEntityCounts();
            } else if (chosenGroupedReferenceIds.length > 0) {
                $('#no-result-bloc').slideDown('fast');
                Hn_RechercheBundle_Referencement_Filter_Category.removeEntityCounts();
            }
        }
    });
    if (chosenGroupedReferenceIds.length == 0) {
        $('.results-bloc > div').slideUp('fast');
        $('#filtres-info').slideDown('fast');
    }

    Hn_RechercheBundle_Referencement_Filter_Category.processFilterDisplaying();
    Hn_RechercheBundle_Referencement.processFilterButtonsActivating();
    Hn_RechercheBundle_Referencement.displayDomaineResults();
    Hn_RechercheBundle_Referencement.saveSession();
};

/**
 * Retourne le HTML d'une entité.
 *
 * @param Array entityProperties Properties
 * @return string HTML
 */
Hn_RechercheBundle_Referencement.getEntityBlocHtml = function(entityProperties)
{
    var html = '';

    html += '<div class="category">' + entityProperties['categoryLabels'] + '</div>';
    html += '<h3><a href="' + entityProperties['url'] + '">' + entityProperties['title'] + '</a></h3>';
    html += '<p>' + entityProperties['description'] + '</p>';

    return html;
};

/**
 * Affiche plus de résultats.
 *
 * @param string resultsGroup Groupe des résultats
 */
Hn_RechercheBundle_Referencement.displayMoreResults = function(resultsGroup)
{
    $('#results-' + resultsGroup + '-more-button').prop('disabled', true);
    var entitiesContainers = $('#results-' + resultsGroup + ' [data-visible="false"]');
    var entitiesByTypeToGet = {};
    var entityIdsByTypeToShow = {};

    $(entitiesContainers).each(function (i, entityContainer) {
        var entityType = $(entityContainer).attr('data-entity-type');
        var entityId = $(entityContainer).attr('data-entity-id');
        var pertinenceNiveau = $(entityContainer).attr('data-pertinence-niveau');
        var isInitialized = ('true' === $(entityContainer).attr('data-initialized'));

        if (undefined == entityIdsByTypeToShow[entityType]) {
            entityIdsByTypeToShow[entityType] = [];
        }
        entityIdsByTypeToShow[entityType].push(entityId);

        if (!isInitialized) {
            if (undefined == entitiesByTypeToGet[entityType]) {
                entitiesByTypeToGet[entityType] = {};
            }
            entitiesByTypeToGet[entityType][entityId] = {
                pertinenceNiveau: pertinenceNiveau
            };
        }

        if (i == Hn_RechercheBundle_Referencement.RESULTS_RANGE - 1) {
            return false;
        }
    });

    if (Object.keys(entitiesByTypeToGet).length > 0) {
        $.ajax({
            url: Routing.generate('hopitalnumerique_recherche_referencement_jsonentities'),
            method: 'POST',
            data: {
                entitiesByType: entitiesByTypeToGet
            },
            dataType: 'json',
            success: function (entitiesByType) {
                for (var entityType in entitiesByType) {
                    for (var entityId in entitiesByType[entityType]) {
                        var entityContainer = $('.results-bloc [data-entity-type="' + entityType + '"][data-entity-id="' + entityId + '"]');
                        $(entityContainer).html(entitiesByType[entityType][entityId]['viewHtml']);
                    }
                }
                for (var entityType in entityIdsByTypeToShow) {
                    for (var i in entityIdsByTypeToShow[entityType]) {
                        Hn_RechercheBundle_Referencement.displayEntity(resultsGroup, entityType, entityIdsByTypeToShow[entityType][i]);
                    }
                }
            }
        });
    } else {
        for (var entityType in entityIdsByTypeToShow) {
            for (var i in entityIdsByTypeToShow[entityType]) {
                Hn_RechercheBundle_Referencement.displayEntity(resultsGroup, entityType, entityIdsByTypeToShow[entityType][i]);
            }
        }
    }
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
