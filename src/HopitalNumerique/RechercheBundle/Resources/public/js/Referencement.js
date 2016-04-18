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
    return $('.references-bloc [data-reference="' + referenceId + '"] a.reference').text();
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
};
//-->
