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
    Hn_RechercheBundle_Referencement.processFilterButtonsActivating();
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
            $('.references-bloc [data-chosen="true"]').attr('data-chosen', 'false');
            Hn_RechercheBundle_Referencement.initReferenceFilters();
        }
    });
};
