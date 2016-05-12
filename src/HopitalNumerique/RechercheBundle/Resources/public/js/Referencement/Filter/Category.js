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
    Hn_RechercheBundle_Referencement_Filter_Category.initEvents();
};

/**
 * Initialisation du multiselect.
 */
Hn_RechercheBundle_Referencement_Filter_Category.initFilterCategoriesSelect = function()
{
    $('#entity-categories').multiselect({
        nonSelectedText: 'Filtrer par type de production ',
        buttonContainer: '<div class="btn-group" />',
        numberDisplayed: 1,
        buttonWidth: '100%',
        nSelectedText: 'catégories sélectionnées'
    });
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
 * Spécifie les ID des types d'entité.
 *
 * @param Array<integer>|null entityTypeIds IDs
 */
Hn_RechercheBundle_Referencement_Filter_Category.setEntityTypeIds = function(entityTypeIds)
{
    $('#entity-categories [data-entity-type]:selected').prop('selected', false);

    if (null !== entityTypeIds) {
        $.each(entityTypeIds, function(i, entityTypeId) {
            $('#entity-categories [data-entity-type="' + entityTypeId +'"]').prop('selected', true);
        });
    }
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
 * Spécifie les ID des catégories de publication.
 *
 * @param Array<integer>|null publicationCategoryIds IDs
 */
Hn_RechercheBundle_Referencement_Filter_Category.setPublicationCategoryIds = function(publicationCategoryIds)
{
    $('#entity-categories [data-reference]:selected').prop('selected', false);

    if (null !== publicationCategoryIds) {
        $.each(publicationCategoryIds, function(i, publicationCategoryId) {
            $('#entity-categories [data-reference="' + publicationCategoryId +'"]').prop('selected', true);
        });
    }
};

/**
 * Affiche ou pas les catégories selon les filtres choisis.
 */
Hn_RechercheBundle_Referencement_Filter_Category.processFilterDisplaying = function()
{
    var hasChosenReferences = Hn_RechercheBundle_Referencement.getChosenElements().size() > 0;
    var hasSearch = (hasChosenReferences || Hn_RechercheBundle_Referencement_Filter_Exalead.hasSearch());
    var filterIsDisplayed = ('block' === $('#entity-categories-container').css('display'));

    if (hasSearch && !filterIsDisplayed) {
        $('#entity-categories-container').slideDown();
    } else if (!v && filterIsDisplayed) {
        $('#entity-categories-container').slideUp();
    }
};
