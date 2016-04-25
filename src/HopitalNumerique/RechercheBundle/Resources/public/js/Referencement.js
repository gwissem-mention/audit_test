/**
 * Gestion de la recherche avancée (par référencement).
 */
var Hn_RechercheBundle_Referencement = function() {};


/**
 * @param int Nombre de résultats à afficher
 */
Hn_RechercheBundle_Referencement.RESULTS_RANGE = 10;


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
    $('.recherche-referencement .add').click(function(event) {
        Hn_RechercheBundle_Referencement.toggleReferenceChoosing(Hn_RechercheBundle_Referencement.getReferenceIdByElement($(this)));
        event.stopPropagation();
    });
    $('.recherche-referencement a.reference').click(function() {
        Hn_RechercheBundle_Referencement.toggleReferenceDisplaying(Hn_RechercheBundle_Referencement.getReferenceIdByElement($(this)));
    });
};


//<-- Accesseurs / mutateurs
/**
 * Retourne l'ID de référence d'un élément.
 */
Hn_RechercheBundle_Referencement.getReferenceIdByElement = function(element)
{
    if (null != $(element).attr('data-reference')) {
        return parseInt($(element).attr('data-reference'));
    }

    if (null != $(element).parent()) {
        return Hn_RechercheBundle_Referencement.getReferenceIdByElement($(element).parent());
    }

    return null;
};

/**
 * Retourne le libellé d'une référence.
 *
 * @return string Libellé
 */
Hn_RechercheBundle_Referencement.getReferenceLibelleById = function(referenceId)
{
    return $('.references-bloc [data-reference="' + referenceId + '"] a.reference').first().text().trim();
};

/**
 * Retourne les ID des références choisies.
 *
 * @return Array<integer> IDs
 */
Hn_RechercheBundle_Referencement.getChosenReferenceIds = function()
{
    var referenceIds = new Array();

    $('.references-bloc [data-chosen="true"]').each(function (i, element) {
        referenceIds.push(Hn_RechercheBundle_Referencement.getReferenceIdByElement(element));
    });

    return referenceIds;
};
//-->


//<-- Arbre des références
/**
 * Plie / déplie les enfants d'une référence.
 */
Hn_RechercheBundle_Referencement.toggleReferenceDisplaying = function(referenceId)
{
    var referenceChildrenList = $('[data-reference="' + referenceId + '"] ul').first();
    var referenceLink = $('[data-reference="' + referenceId + '"] .reference').first();

    if ($(referenceChildrenList).size() > 0) {
        var chevron = $(referenceLink).find('.toggle .fa');

        if ('none' === $(referenceChildrenList).css('display')) {
            $(referenceChildrenList).slideDown();

            $(chevron).removeClass('fa-chevron-right');
            $(chevron).addClass('fa-chevron-down');
        } else {
            $(referenceChildrenList).slideUp();

            $(chevron).removeClass('fa-chevron-down');
            $(chevron).addClass('fa-chevron-right');
        }
    }
};

/**
 * Ajoute / enlève une référence pour la recherche.
 */
Hn_RechercheBundle_Referencement.toggleReferenceChoosing = function(referenceId)
{
    var referenceIsChosen = ('true' === $('[data-reference="' + referenceId + '"]').attr('data-chosen'));

    $('[data-reference="' + referenceId + '"]').attr('data-chosen', referenceIsChosen ? 'false' : 'true');
    Hn_RechercheBundle_Referencement.initReferenceFilters();
};
//-->

//<-- Filtres de recherche
/**
 * Initialise les filtres.
 */
Hn_RechercheBundle_Referencement.initReferenceFilters = function()
{
    var filtersHtml = '';
    var chosenReferenceIds = Hn_RechercheBundle_Referencement.getChosenReferenceIds();
    for (var i in chosenReferenceIds) {
        var referenceId = chosenReferenceIds[i];
        filtersHtml += '<li data-reference="' + referenceId + '">' + Hn_RechercheBundle_Referencement.getReferenceLibelleById(referenceId) + ' <a onclick="Hn_RechercheBundle_Referencement.toggleReferenceChoosing(' + referenceId + ');" class="remove fa fa-times"></a></li> ';
    }

    $('.filtres-bloc .references ul').css({ display: 'none' });
    $('.filtres-bloc .references ul').html(filtersHtml);
    $('.filtres-bloc .references ul').fadeIn('slow');

    Hn_RechercheBundle_Referencement.displayResults();
};
//-->


//<-- Résultats
/**
 * Affiche les résultats.
 */
Hn_RechercheBundle_Referencement.displayResults = function()
{
    $.ajax({
        url: Routing.generate('hopitalnumerique_recherche_referencement_jsonentitiesbyreferences'),
        method: 'post',
        type: 'json',
        data: {
            'references': Hn_RechercheBundle_Referencement.getChosenReferenceIds()
        },
        success: function(data) {
            for (var group in data) {
                $('#results-' + group + '-bloc').css({ display: (data[group].length > 0 ? 'block' : 'none') });
                if (data[group].length > 0) {
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
    });
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
//-->
