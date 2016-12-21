$(document).ready(function() {
    $('#research-list').change( function() {
        location.href = $(this).val();
    });

    $('.request-button').tooltip();
});

Hn_RechercheBundle_Referencement.saveSessionTimer;

/**
 * Initialise les filtres.
 */
Hn_RechercheBundle_Referencement.initReferenceFilters = function()
{
    $('.filtres-bloc .references ul').empty();
    $('.filtres-bloc .references').hide();
    $('.request-button').css({ display: 'none' });

    var chosenReferenceIds = Hn_RechercheBundle_Referencement.getChosenReferenceIds();
    for (var i in chosenReferenceIds) {
        var referenceId = chosenReferenceIds[i];

        var filter = $('<li />')
            .attr('data-reference', referenceId)
            .html(
                Hn_RechercheBundle_Referencement.getReferenceLibelleById(referenceId) + ' <a onclick="Hn_RechercheBundle_Referencement.toggleReferenceChoosing(' + referenceId + ');Hn_RechercheBundle_Referencement.initReferenceFilters();" class="remove fa fa-times"></a>'
            );

        filter.tooltip({
            title: Hn_RechercheBundle_Referencement.getReferenceFilterTitle(referenceId),
            html: true
        });

        $('.filtres-bloc .references').show();
        $('.request-button').show();
        $('.filtres-bloc .references ul').append(filter);
    }

    $('.filtres-bloc .references ul').css({ display: 'none' });
    $('.filtres-bloc .references ul').fadeIn('slow');

    Hn_RechercheBundle_Referencement.displayResults();
};

Hn_RechercheBundle_Referencement.getReferenceFilterTitle = function(referenceId)
{
    var title = [];
    var parent = Hn_RechercheBundle_Referencement.getReferenceParentIdByReferenceId(referenceId);
    while (null !== parent) {
        var element = Hn_RechercheBundle_Referencement.getElementByReferenceId(parent);
        if (element.data('libelle') !== undefined) {
            title.unshift(element.data('libelle'));
        }
        parent = Hn_RechercheBundle_Referencement.getReferenceParentIdByReferenceId(parent);
    }

    return title.join(' > ');
};

/**
 * Active ou pas les boutons du filtre.
 */
Hn_RechercheBundle_Referencement.processFilterButtonsActivating = function()
{
    var choosenReferenceIds = Hn_RechercheBundle_Referencement.getChosenReferenceIds();
    var filterButtonsVisible = ('none' !== $('#filtres-actions').css('display'));
    var canActivate = (choosenReferenceIds.length > 0 || Hn_RechercheBundle_Referencement_Filter_Exalead.hasSearch());

    if (canActivate && !filterButtonsVisible) {
        $('#filtres-actions').slideDown();
        $('.request-button').fadeIn();
    } else if (!canActivate && filterButtonsVisible) {
        $('#filtres-actions').slideUp();
        $('.request-button').fadeOut();
    }
};

/**
 * Enlève tous les filtres.
 */
Hn_RechercheBundle_Referencement.removeFilters = function()
{
    apprise('Confirmer la réinitialisation de la recherche ?', { 'verify':true,'textYes':'Oui','textNo':'Non' }, function (response) {
        if (response) {
            Hn_RechercheBundle_Referencement.getChosenElements().attr('data-chosen', 'false');
            Hn_RechercheBundle_Referencement_Filter_Exalead.setSearchedText('');
            Hn_RechercheBundle_Referencement_Filter_Category.clear();
            Hn_RechercheBundle_Referencement.initReferenceFilters();
            $.ajax({
                url: Routing.generate('hopitalnumerique_recherche_referencement_requete_removesession'),
                method: 'POST'
            });
            $('#filtres-actions #search-name').hide();
            $('.request-button').hide();
            $('#research-list').val("#");
        }
    });
};

/**
 * Sauvegarde les filtres choisis.
 */
Hn_RechercheBundle_Referencement.saveFilters = function()
{
    var chooseReferenceIds = Hn_RechercheBundle_Referencement.getChosenReferenceIds();
    var canSave = (chooseReferenceIds.length > 0 || Hn_RechercheBundle_Referencement_Filter_Exalead.hasSearch());

    if (canSave) {
        Hn_RechercheBundle_Referencement.saveSession();
        $.ajax({
            url: Routing.generate('hopitalnumerique_recherche_referencement_requete_popinsave'),
            method: 'POST',
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
    if (Hn_RechercheBundle_Referencement.saveSessionTimer !== undefined) {
        clearTimeout(Hn_RechercheBundle_Referencement.saveSessionTimer);
    }

    // Timeout pour ne pas ralentir la recherche
    Hn_RechercheBundle_Referencement.saveSessionTimer = setTimeout(function() {
        $.ajax({
            url: Routing.generate('hopitalnumerique_recherche_referencement_requete_savesession'),
            method: 'POST',
            data: {
                referenceIds: Hn_RechercheBundle_Referencement.getChosenReferenceIds(),
                entityTypesIds: Hn_RechercheBundle_Referencement_Filter_Category.getEntityTypeIds(),
                publicationCategoryIds: Hn_RechercheBundle_Referencement_Filter_Category.getPublicationCategoryIds(),
                searchedText: Hn_RechercheBundle_Referencement_Filter_Exalead.getSearchedText(),
                resultsCount: Hn_RechercheBundle_Referencement.count()
            }
        });
        Hn_RechercheBundle_Referencement.saveSessionTimer = undefined;
    }, 1500);
};
