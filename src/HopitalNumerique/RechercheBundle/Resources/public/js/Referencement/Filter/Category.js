/**
 * Gestion du filtre des catégories.
 */
var Hn_RechercheBundle_Referencement_Filter_Category = function() {};


$(document).ready(function() {
    Hn_RechercheBundle_Referencement_Filter_Category.init();
});


/**
 * Initialisation.
 */
Hn_RechercheBundle_Referencement_Filter_Category.init = function()
{
    $('#entity-categories').multiselect({
        nonSelectedText: 'Filtrer par type de production ',
        buttonContainer: '<div class="btn-group" />',
        numberDisplayed: 1,
        buttonWidth: '100%',
        nSelectedText: 'catégories sélectionnées'
    });
    Hn_RechercheBundle_Referencement_Filter_Category.initEvents();
};

/**
 * Initialisation des événements.
 */
Hn_RechercheBundle_Referencement_Filter_Category.initEvents = function()
{
    $('#entity-categories').change(function() {
        Hn_RechercheBundle_Referencement.displayResults();
    });
};


/**
 * Retourne si un filtre est choisi.
 *
 * @return boolean Si filtre
 */
Hn_RechercheBundle_Referencement_Filter_Category.hasFilter = function()
{
    return (Hn_RechercheBundle_Referencement_Filter_Category.getPublicationCategoryIds().length > 0 || Hn_RechercheBundle_Referencement_Filter_Category.getEntityTypeIds().length > 0);
};

/**
 * Récupère les IDs des catégories de publication du filtre.
 *
 * @return array<integer> IDs
 */
Hn_RechercheBundle_Referencement_Filter_Category.getPublicationCategoryIds = function()
{
    var publicationCategoryIds = [];

    $('#entity-categories [data-reference]:selected').each(function(i, option) {
        publicationCategoryIds.push(parseInt($(option).attr('data-reference')));
    });

    return publicationCategoryIds;
};

/**
 * Récupère les IDs des types d'entités du filtre.
 *
 * @return array<integer> IDs
 */
Hn_RechercheBundle_Referencement_Filter_Category.getEntityTypeIds = function()
{
    var entityTypeIds = [];

    $('#entity-categories [data-entity-type]:selected').each(function(i, option) {
        entityTypeIds.push(parseInt($(option).attr('data-entity-type')));
    });

    return entityTypeIds;
};

/**
 * Affiche ou pas les catégories selon les filtres choisis.
 */
Hn_RechercheBundle_Referencement_Filter_Category.processFilterDisplaying = function()
{
    var hasChosenReferences = Hn_RechercheBundle_Referencement.getChosenElements().size() > 0;
    var filterIsDisplayed = ('block' === $('#entity-categories-container').css('display'));

    if (hasChosenReferences && !filterIsDisplayed) {
        $('#entity-categories-container').slideDown();
    } else if (!hasChosenReferences && filterIsDisplayed) {
        $('#entity-categories-container').slideUp();
    }
};
