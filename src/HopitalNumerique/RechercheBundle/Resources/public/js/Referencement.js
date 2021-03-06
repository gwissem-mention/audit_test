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
    Hn_RechercheBundle_Referencement.initTreeDisplay();
    Hn_RechercheBundle_Referencement.initFancyBoxOnSynthesis();
};

/**
 * Initialise l'affichage de l'arbre de références
 */
Hn_RechercheBundle_Referencement.initTreeDisplay = function()
{
    // Ouvre la première référence de niveau 1
    Hn_RechercheBundle_Referencement.toggleReferenceDisplaying(
        Hn_RechercheBundle_Referencement.getReferenceIdByElement(
            $('[data-reference][data-level=1]').first()
        )
    );
};

/**
 * Initialisation des événements.
 */
Hn_RechercheBundle_Referencement.initEvents = function()
{
    // Ajout d'une référence
    $('.recherche-referencement .add').click(function(event) {
        Hn_RechercheBundle_Referencement.toggleReferenceChoosing(Hn_RechercheBundle_Referencement.getReferenceIdByElement($(this)));
        event.stopPropagation();
    });
    // Pliage / dépliage
    $('.recherche-referencement a.reference').click(function() {
        Hn_RechercheBundle_Referencement.toggleReferenceDisplaying(Hn_RechercheBundle_Referencement.getReferenceIdByElement($(this)));
    });
};

/**
 * Initialisation de la FancyBox sur les liens redirigeant vers la synthèse
 */
Hn_RechercheBundle_Referencement.initFancyBoxOnSynthesis = function()
{
    $('a.synthesis').fancybox({
        'padding': 0,
        'autoSize': false,
        'width': '80%',
        'scrolling': 'no'
    });
};

//<-- Accesseurs / mutateurs
Hn_RechercheBundle_Referencement.getElementByReferenceId = function(referenceId)
{
    return $('.references-bloc [data-reference="' + referenceId + '"], #contexte-modal [data-reference="' + referenceId + '"]');
};

Hn_RechercheBundle_Referencement.getReferenceParentIdByReferenceId = function(referenceId)
{
    var elementParent = Hn_RechercheBundle_Referencement.getElementByReferenceId(referenceId).parent().parent();

    return Hn_RechercheBundle_Referencement.getReferenceIdByElement(elementParent);
};

/**
 * Retourne l'ID de référence d'un élément.
 */
Hn_RechercheBundle_Referencement.getReferenceIdByElement = function(element)
{
    if (null != $(element).attr('data-reference')) {
        return parseInt($(element).attr('data-reference'));
    }

    if (1 == $(element).parent().size()) {
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
    if ($('.references-bloc [data-reference="' + referenceId + '"]').size() > 0) {
        return $('.references-bloc [data-reference="' + referenceId + '"] a.reference').first().text().trim();
    } else { // Mon contexte
        var $e = $('#contexte-modal [data-reference="' + referenceId + '"]');

        return $e.attr('data-libelle');
    }
};

/**
 * Retourne les éléments des références choisies.
 *
 * @param Element element (optionnel) Si défini, retourne uniquement les éléments choisis sous cet élément
 * @return Array<Element> Éléments
 */
Hn_RechercheBundle_Referencement.getChosenElements = function(element)
{
    if (undefined !== element) {
        return $(element).find('[data-chosen="true"]');
    }

    return $('.references-bloc [data-chosen="true"], #contexte-modal [data-chosen="true"]');
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

Hn_RechercheBundle_Referencement.referenceIdIsChosen = function(referenceId)
{
    return (
        'true' == Hn_RechercheBundle_Referencement.getElementByReferenceId(referenceId).attr('data-chosen')
        || 1 == $('#contexte-modal [data-reference="' + referenceId + '"] input:checked').size()
    );
};

/**
 * Retourne les ID des références choisies par regroupement (pour chaque référence de niveau 1).
 *
 * @return Array IDs
 */
Hn_RechercheBundle_Referencement.getChosenGroupedReferenceIds = function()
{
    var groupedChosenReferenceIds = [];

    $('[data-level="1"]').each(function(i, tree) {
        var referenceIds = [];

        $(tree).find('[data-chosen="true"]').each(function(j, element) {
            referenceIds.push(parseInt($(element).attr('data-reference')));
        });

        if (referenceIds.length > 0) {
            groupedChosenReferenceIds.push(referenceIds);
        }
    });

    return groupedChosenReferenceIds;
};

/**
 * Retourne le niveau d'une référence.
 *
 * @param integer referenceId ID de la référence
 * @return integer Niveau
 */
Hn_RechercheBundle_Referencement.getLevelByReferenceId = function(referenceId)
{
    return parseInt(Hn_RechercheBundle_Referencement.getElementByReferenceId(referenceId).attr('data-level'));
};
//-->


//<-- Arbre des références
/**
 * Plie / déplie les enfants d'une référence.
 *
 * @param integer referenceId ID de la référence
 */
Hn_RechercheBundle_Referencement.toggleReferenceDisplaying = function(referenceId)
{
    var referenceChildrenList = Hn_RechercheBundle_Referencement.getReferenceChildrenElement(referenceId);
    var referenceLink = $('[data-reference="' + referenceId + '"] .reference').first();
    var referenceLevel = Hn_RechercheBundle_Referencement.getLevelByReferenceId(referenceId);

    var chevron = $(referenceLink).find('.toggle .fa');

    if (!Hn_RechercheBundle_Referencement.referenceChildrenAreDisplayed(referenceId)) {
        Hn_RechercheBundle_Referencement.showReferenceChildren(referenceId);

        if (1 === referenceLevel) { // On cache les autres si ouvre premier niveau
            var referencesNiveau1 = $('.references-bloc [data-level="' + referenceLevel + '"]');
            $(referencesNiveau1).each(function (i, element) {
                var otherReferenceId = Hn_RechercheBundle_Referencement.getReferenceIdByElement(element);
                if (referenceId !== otherReferenceId && Hn_RechercheBundle_Referencement.referenceChildrenAreDisplayed(otherReferenceId)) {
                    Hn_RechercheBundle_Referencement.hideReferenceChildren(otherReferenceId);
                }
            });
        }
    } else {
        Hn_RechercheBundle_Referencement.hideReferenceChildren(referenceId);
    }
};

/**
 * Affiche le sous-arbre.
 *
 * @param integer referenceId ID de la référence
 */
Hn_RechercheBundle_Referencement.showReferenceChildren = function(referenceId)
{
    var referenceLink = $('[data-reference="' + referenceId + '"] .reference').first();
    var chevron = $(referenceLink).find('.toggle .fa');

    Hn_RechercheBundle_Referencement.getReferenceChildrenElement(referenceId).slideDown();

    $(chevron).removeClass('fa-chevron-right');
    $(chevron).addClass('fa-chevron-down');
};

/**
 * Cache le sous-arbre.
 *
 * @param integer referenceId ID de la référence
 */
Hn_RechercheBundle_Referencement.hideReferenceChildren = function(referenceId)
{
    var referenceLink = $('[data-reference="' + referenceId + '"] .reference').first();
    var chevron = $(referenceLink).find('.toggle .fa');

    Hn_RechercheBundle_Referencement.getReferenceChildrenElement(referenceId).slideUp();

    $(chevron).removeClass('fa-chevron-down');
    $(chevron).addClass('fa-chevron-right');
};

/**
 * Retourne le sous-arbre d'une référence.
 *
 * @param integer referenceId ID de la référence
 * @returns Element Sous-arbre
 */
Hn_RechercheBundle_Referencement.getReferenceChildrenElement = function(referenceId)
{
    return $('[data-reference="' + referenceId + '"] ul').first();
};

/**
 * Retourne si le sous-arbre d'une référence est visible.
 *
 * @param integer referenceId ID de la référence
 * @returns boolean Si visible
 */
Hn_RechercheBundle_Referencement.referenceChildrenAreDisplayed = function(referenceId)
{
    var referenceChildrenList = Hn_RechercheBundle_Referencement.getReferenceChildrenElement(referenceId);

    if ($(referenceChildrenList).size() > 0) {
        return ('none' !== $(referenceChildrenList).css('display'));
    }

    return false;
};

/**
 * Ajoute / enlève une référence pour la recherche.
 */
Hn_RechercheBundle_Referencement.toggleReferenceChoosing = function(referenceId)
{
    // Gestion du double click
    if (Hn_RechercheBundle_Referencement.getElementByReferenceId(referenceId).data('just-chosen') !== undefined) {
        return false;
    }
    Hn_RechercheBundle_Referencement.getElementByReferenceId(referenceId).data('just-chosen', true);
    setTimeout(function () {
        Hn_RechercheBundle_Referencement.getElementByReferenceId(referenceId).removeData('just-chosen');
    }, 500);

    var referenceIsChosen = ('true' === Hn_RechercheBundle_Referencement.getElementByReferenceId(referenceId).attr('data-chosen'));
    Hn_RechercheBundle_Referencement.getElementByReferenceId(referenceId).attr('data-chosen', referenceIsChosen ? 'false' : 'true');

    var referenceCheckbox = $('#contexte-modal [data-reference="' + referenceId + '"] input');
    if ($(referenceCheckbox).size() === 1) { // Popin Mon contexte
        $('#contexte-modal [data-reference="' + referenceId + '"] input').prop('checked', referenceIsChosen ? false : true);
    } else { // Menu de gauche
        if (!referenceIsChosen) {
            // On décoche tous les enfants (récupérés automatiquement dans la requête)
            Hn_RechercheBundle_Referencement.getChosenElements(Hn_RechercheBundle_Referencement.getElementByReferenceId(referenceId))
                .each(function (i, chosenElement) {
                    Hn_RechercheBundle_Referencement.toggleReferenceChoosing(Hn_RechercheBundle_Referencement.getReferenceIdByElement(chosenElement));
                });
            // On décoche tous les parents (récupérés automatiquement dans la requête)
            var referenceParentId = Hn_RechercheBundle_Referencement.getReferenceParentIdByReferenceId(referenceId);
            while (null != referenceParentId) {
                if (Hn_RechercheBundle_Referencement.referenceIdIsChosen(referenceParentId)) {
                    Hn_RechercheBundle_Referencement.toggleReferenceChoosing(referenceParentId);
                    break;
                } else {
                    referenceParentId = Hn_RechercheBundle_Referencement.getReferenceParentIdByReferenceId(referenceParentId);
                }
            }
        }
    }

    $('#contexte-modal select option[data-chosen="true"]').attr('selected', true);

    Hn_RechercheBundle_Referencement.initReferenceFilters();
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
