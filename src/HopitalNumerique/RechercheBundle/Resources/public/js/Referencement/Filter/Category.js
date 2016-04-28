/**
 * Gestion du filtre des catégories.
 */
var Hn_RechercheBundle_Referencement_Filter_Category = function() {};


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

