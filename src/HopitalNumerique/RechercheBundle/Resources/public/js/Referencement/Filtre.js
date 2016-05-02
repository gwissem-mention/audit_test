/**
 * Initialise les filtres.
 */
Hn_RechercheBundle_Referencement.initReferenceFilters = function()
{
    var filtersHtml = '';
    var chosenReferenceIds = Hn_RechercheBundle_Referencement.getChosenReferenceIds();
    for (var i in chosenReferenceIds) {
        var referenceId = chosenReferenceIds[i];
        filtersHtml += '<li data-reference="' + referenceId + '">' + Hn_RechercheBundle_Referencement.getReferenceLibelleById(referenceId) + ' <a onclick="Hn_RechercheBundle_Referencement.toggleReferenceChoosing(' + referenceId + ');Hn_RechercheBundle_Referencement.initReferenceFilters();" class="remove fa fa-times"></a></li> ';
    }

    $('.filtres-bloc .references ul').css({ display: 'none' });
    $('.filtres-bloc .references ul').html(filtersHtml);
    $('.filtres-bloc .references ul').fadeIn('slow');

    Hn_RechercheBundle_Referencement.displayResults();
};

/**
 * Active ou pas les boutons du filtre.
 */
Hn_RechercheBundle_Referencement.processFilterButtonsActivating = function()
{
    var choosenReferenceIds = Hn_RechercheBundle_Referencement.getChosenReferenceIds();
    var filterButtonsVisible = ('none' !== $('#filtres-actions').css('display'));

    if (choosenReferenceIds.length > 0 && !filterButtonsVisible) {
        $('#filtres-actions').slideDown();
    } else if (0 === choosenReferenceIds.length && filterButtonsVisible) {
        $('#filtres-actions').slideUp();
    }
};

/**
 * Enlève tous les filtres.
 */
Hn_RechercheBundle_Referencement.removeFilters = function()
{
    apprise('Confirmer la réinitialisation de la requête ?', { 'verify':true,'textYes':'Oui','textNo':'Non' }, function (response) {
        if (response) {
            Hn_RechercheBundle_Referencement.getChosenElements().attr('data-chosen', 'false');
            Hn_RechercheBundle_Referencement.initReferenceFilters();
        }
    });
};

/**
 * Sauvegarde les filtres choisis.
 */
Hn_RechercheBundle_Referencement.saveFilters = function()
{
    var chooseReferenceIds = Hn_RechercheBundle_Referencement.getChosenReferenceIds();

    if (chooseReferenceIds.length > 0) {
        $.ajax({
            url: Routing.generate('hopitalnumerique_recherche_referencement_requete_popinsave'),
            method: 'POST',
            data: {
                referenceIds: chooseReferenceIds,
                entityTypesIds: Hn_RechercheBundle_Referencement_Filter_Category.getEntityTypeIds(),
                publicationCategoryIds: Hn_RechercheBundle_Referencement_Filter_Category.getPublicationCategoryIds()
            },
            success: function (data) {
                $.fancybox.open(data);
            }
        });
    }
};

/**
 * Sauvegarde les filtres choisis en session.
 */
Hn_RechercheBundle_Referencement.saveSession = function()
{
    $.ajax({
        url: Routing.generate('hopitalnumerique_recherche_referencement_requete_savesession'),
        method: 'POST',
        data: {
            referenceIds: Hn_RechercheBundle_Referencement.getChosenReferenceIds(),
            entityTypesIds: Hn_RechercheBundle_Referencement_Filter_Category.getEntityTypeIds(),
            publicationCategoryIds: Hn_RechercheBundle_Referencement_Filter_Category.getPublicationCategoryIds()
        }
    });
};
