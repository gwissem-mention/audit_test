/**
 * Enlève les nombres de résultats.
 */
Hn_RechercheBundle_Referencement_Filter_Category.removeEntityCounts = function()
{
    var countElements = $('#entity-categories-container').find('[id$="-count"]');

    $(countElements).css({ display:'none' });
    $(countElements).parent().parent().css({ display:'block' });
};

/**
 * Affiche le nombre d'entités pour chaque catégorie.
 */
Hn_RechercheBundle_Referencement_Filter_Category.displayEntityCounts = function()
{
    $('#entity-categories-container .multiselect-container input[type="checkbox"]').each(function (i, element) {
        var inputVal = $(element).val();
        var elementCountId = inputVal + '-count';
        var elementId = inputVal.substr(inputVal.indexOf('-') + 1);

        if ($(element).parent().find('#' + elementCountId).size() === 0) {
            $(element).parent().append(' <span id="' + elementCountId + '"></span>');
        }

        if ('pc-' === inputVal.substr(0, 3)) { // Catégorie de production
            Hn_RechercheBundle_Referencement_Filter_Category.displayCategoryCounts(elementId);
        } else if ('t-' === inputVal.substr(0, 2)) { // Type d'entité
            Hn_RechercheBundle_Referencement_Filter_Category.displayEntityTypeCounts(elementId);
        }
    });
};

/**
 * Affiche le nombre de résultats d'un type d'entité.
 *
 * @param integer entityType Type d'entité
 */
Hn_RechercheBundle_Referencement_Filter_Category.displayEntityTypeCounts = function(entityType)
{
    var countElement = $('#entity-categories-container #t-' + entityType + '-count');
    var countElementContainer = $(countElement).parent().parent();
    var count = Hn_RechercheBundle_Referencement_Filter_Category.getEntityCountByEntityType(entityType);

    $(countElementContainer).css({ display:(count > 0 ? 'block' : 'none') });
    $(countElement).html(count);
    $(countElement).css({ display:'inline' });
};

/**
 * Affiche le nombre de résultats d'une catégorie.
 *
 * @param integer categoryId ID de la catégorie
 */
Hn_RechercheBundle_Referencement_Filter_Category.displayCategoryCounts = function(categoryId)
{
    var countElement = $('#entity-categories-container #pc-' + categoryId + '-count');
    var countElementContainer = $(countElement).parent().parent();
    var count = Hn_RechercheBundle_Referencement_Filter_Category.getEntityCountByCategoryId(categoryId);

    $(countElementContainer).css({ display:(count > 0 ? 'block' : 'none') });
    $(countElement).html(count);
    $(countElement).css({ display:'inline' });
};

/**
 * Retourne le nombre de résultats d'un type d'entité.
 *
 * @param integer entityType Type d'entité
 */
Hn_RechercheBundle_Referencement_Filter_Category.getEntityCountByEntityType = function(entityType)
{
    return $('.results-bloc [data-entity-type="' + entityType + '"]').size();
};

/**
 * Retourne le nombre de résultats d'une catégorie.
 *
 * @param integer categoryId ID de la catégorie
 */
Hn_RechercheBundle_Referencement_Filter_Category.getEntityCountByCategoryId = function(categoryId)
{
    return $('.results-bloc [data-categories~="' + categoryId + '"]').size();
};
