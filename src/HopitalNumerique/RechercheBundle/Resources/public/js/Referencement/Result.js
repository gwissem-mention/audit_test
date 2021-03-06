/**
 * @var Element Ajax loader
 */
Hn_RechercheBundle_Referencement.AJAX_LOADER = null;

/**
 * @var boolean Si une recherche est en cours
 */
Hn_RechercheBundle_Referencement.IS_SEARCHING = false;
Hn_RechercheBundle_Referencement.mainSearchQuery = null;
Hn_RechercheBundle_Referencement.showCog = false;

/**
 * Affiche les résultats.
 */
Hn_RechercheBundle_Referencement.displayResults = function()
{
    Hn_RechercheBundle_Referencement.desactiveSearch();

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

    Hn_RechercheBundle_Referencement.mainSearchQuery = $.ajax({
        url: Routing.generate('hopitalnumerique_recherche_referencement_jsonentitiesbyreferences'),
        method: 'post',
        type: 'json',
        data: ajaxOptions,
    }).done(
        function (data, status, jqXHR) {
            if (Hn_RechercheBundle_Referencement.mainSearchQuery !== jqXHR) {
                return true;
            }

            if (data['showCog']) {
                Hn_RechercheBundle_Referencement.showCog = true;
            }

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

            if (totalCount == 0 && (Hn_RechercheBundle_Referencement_Filter_Exalead.hasSearch() || chosenGroupedReferenceIds.length > 0)) {
                $('#no-result-bloc').slideDown('fast');
            } else {
                $('#no-result-bloc').slideUp('fast');
            }

            if (totalCount > 0) {
                $('#results-count').html('(' + totalCount + ' résultat' + (totalCount > 1 ? 's' : '') + ')');
                Hn_RechercheBundle_Referencement_Filter_Category.displayEntityCounts();
            } else if (chosenGroupedReferenceIds.length > 0) {
                Hn_RechercheBundle_Referencement_Filter_Category.removeEntityCounts();
            }

            Hn_RechercheBundle_Referencement.activeSearch();
            Hn_RechercheBundle_Referencement_Filter_Exalead.highlightWords(data.foundWords);

        }
    );

    if (chosenGroupedReferenceIds.length == 0) {
        $('.results-bloc > div[id]').slideUp('fast');
        $('#filtres-info').slideDown('fast');
    }

    Hn_RechercheBundle_Referencement_Filter_Category.processFilterDisplaying();
    Hn_RechercheBundle_Referencement.processFilterButtonsActivating();
    Hn_RechercheBundle_Referencement.displayDomaineResults();
    Hn_RechercheBundle_Referencement.saveSession();
};

Hn_RechercheBundle_Referencement.activeSearch = function()
{
    Hn_RechercheBundle_Referencement.IS_SEARCHING = false;
    //$('#search-text-button').prop('disabled', false);
    Hn_RechercheBundle_Referencement_Filter_Exalead.processSearchValidating();
    Hn_RechercheBundle_Referencement.AJAX_LOADER.finished();
};

Hn_RechercheBundle_Referencement.desactiveSearch = function()
{
    Hn_RechercheBundle_Referencement.IS_SEARCHING = true;
    Hn_RechercheBundle_Referencement.AJAX_LOADER = $('.results-bloc').data('loader') !== undefined
        ? $('.results-bloc').data('loader')
        : $('.results-bloc').nodevoLoader();
    Hn_RechercheBundle_Referencement.AJAX_LOADER.start();
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
    if (Hn_RechercheBundle_Referencement.showCog == true) {
        html += '<h3 class="title"><a href="'+ Routing.generate('hopitalnumerique_reference_referencement_popin', { entityType: entityProperties['entityType'], entityId: entityProperties['entityId'] }) +'" class="open-popin-referencement fancybox.ajax pull-left" style="margin-right: 5px;" title="Référencer cette publication"><i class="fa fa-cog"></i></a><a href="' + entityProperties['url'] + '">';
    } else {
        html += '<h3 class="title"><a href="' + entityProperties['url'] + '">';
    }
    html += '<em class="pertinence-niveau-' + entityProperties['pertinenceNiveau'] + '"></em>';
    html += entityProperties['title'];
    if (undefined != entityProperties['subtitle']) {
        html += '<span class="subtitle"><em class="fa fa-share fa-flip-vertical"></em> ' + entityProperties['subtitle'] + '</span>';
    }
    html += '</a></h3>';

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
        var ajaxLoader = $('#results-' + resultsGroup + '-bloc .ajax-loader').nodevoLoader().start();
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

                $(document).trigger('search-results-updated');
                ajaxLoader.finished();

                $('#results-' + resultsGroup + '-bloc .open-popin-referencement').on('click', function (e) {
                    e.preventDefault();
                    Hn_Reference_Referencement_Popin.open($(this).attr('href'));
                });
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

    $('#results-' + resultsGroup + '-less-button')[lessResultsPossible ? "show" : "hide"]();
    $('#results-' + resultsGroup + '-more-button')[moreResultsPossible ? "show" : "hide"]();
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

/**
 * Retourne le nombre de résultats totaux.
 *
 * @param string resultsGroup Groupe des résultats
 * @param int entityType Type d'entité
 * @param int entityiD   ID de l'entité
 */
Hn_RechercheBundle_Referencement.count = function()
{
    return ($('.results-bloc .result').size());
};
