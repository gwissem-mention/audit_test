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
        Hn_RechercheBundle_Referencement.initReferenceFilters();
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
 * Retourne les éléments des références choisies.
 *
 * @return Array<Element> Éléments
 */
Hn_RechercheBundle_Referencement.getChosenElements = function()
{
    return $('.references-bloc [data-chosen="true"]');
};

/**
 * Retourne les ID des références choisies.
 *
 * @return Array<integer> IDs
 */
Hn_RechercheBundle_Referencement.getChosenReferenceIds = function()
{
    var referenceIds = new Array();

    Hn_RechercheBundle_Referencement.getChosenElements().each(function (i, element) {
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

    $('.references-bloc [data-reference="' + referenceId + '"]').attr('data-chosen', referenceIsChosen ? 'false' : 'true');
};

/**
 * Sélection une référence.
 *
 * @param integer referenceId ID de la référence
 */
Hn_RechercheBundle_Referencement.setReferenceIds = function(referenceIds)
{
    Hn_RechercheBundle_Referencement.getChosenElements().attr('data-chosen', 'false');
    for (var i in referenceIds) {
        Hn_RechercheBundle_Referencement.toggleReferenceChoosing(referenceIds[i]);
    }
};
//-->


/**
 * Redirige vers la recherche par référencement d'un autre domaine.
 *
 * @param string domaineUrl URL de l'autre domaine
 */
Hn_RechercheBundle_Referencement.redirectDomaine = function(domaineId)
{
    var referenceString = Hn_RechercheBundle_Referencement.getChosenReferenceIdsByDomaineId(domaineId).join('-');
    Nodevo_Web.redirect(Hn_DomaineBundle_Domaine.getUrlById(domaineId) + Routing.generate('hopitalnumerique_recherche_referencement_indexwithreferences', { referenceString: referenceString }, false));
};

