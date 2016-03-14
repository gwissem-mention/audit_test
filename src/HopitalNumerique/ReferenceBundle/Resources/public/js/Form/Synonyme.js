/**
 * Classe gérant les synonymes dans les formulaires.
 * 
 * @author Rémi Leclerc
 */
var Hn_Reference_Form_Synonyme = function() {};

Hn_Reference_Form_Synonyme.FORM_UL = null;
Hn_Reference_Form_Synonyme.FORM_AJOUT_LIEN = $('<button class="btn btn-sm btn-success" type="button">Ajouter un synonyme &nbsp; <em class="fa fa-plus"></em></button>');
Hn_Reference_Form_Synonyme.FORM_AJOUT_LI = $('<li class="col-lg-3 col-md-4"></li>').append(Hn_Reference_Form_Synonyme.FORM_AJOUT_LIEN);


$(document).ready(function() {
    Hn_Reference_Form_Synonyme.init();
});


/**
 * Initialisation du formulaire d'édition d'un synonyme.
 *
 * @return void
 */
Hn_Reference_Form_Synonyme.init = function()
{
    Hn_Reference_Form_Synonyme.FORM_UL = $('ul#reference_synonymes_form');
    
    Hn_Reference_Form_Synonyme.FORM_UL.find('li').each(function() {
        Hn_Reference_Form_Synonyme.addSuppressionFormLien($(this));
    });
    Hn_Reference_Form_Synonyme.FORM_UL.append(Hn_Reference_Form_Synonyme.FORM_AJOUT_LI);
    Hn_Reference_Form_Synonyme.FORM_AJOUT_LIEN.on('click', function(e) {
        Hn_Reference_Form_Synonyme.addForm();
    });
};

/**
 * Ajoute dans le formulaire un lien permettant de supprimer une valeur attendue.
 * 
 * @param Element elementLi Élément LI que l'on peut supprimer
 * @return void
 */
Hn_Reference_Form_Synonyme.addSuppressionFormLien = function(elementLi)
{
    var suppressionFormLien = $('<button class="btn btn-danger btn-sm" type="button">Supprimer &nbsp; <em class="fa fa-times"></em></button>');
    var suppressionFormLienLayout = $('<p class="text-right"></p>');
    suppressionFormLienLayout.append(suppressionFormLien);
    elementLi.append(suppressionFormLienLayout);
    
    suppressionFormLien.on('click', function(e) {
        if (confirm('Confirmez-vous la suppression de ce synonyme ?')) {
            elementLi.hide('slow', function() { $(this).remove(); });
        }
    });
};
/**
 * Ajoute dans le formulaire un champ de création de valeur attendue.
 * 
 * @return void
 */
Hn_Reference_Form_Synonyme.addForm = function()
{
    var prototype = Hn_Reference_Form_Synonyme.FORM_UL.attr('data-prototype');

    var numeroForm = Hn_Reference_Form_Synonyme.FORM_UL.children().length;
    var nouveauChamp = prototype.replace(/__name__/g, numeroForm);
    var nouveauChampLi = $('<li class="col-lg-3 col-md-4" style="display:none;"></li>').append(nouveauChamp);

    Hn_Reference_Form_Synonyme.addSuppressionFormLien(nouveauChampLi);

    Hn_Reference_Form_Synonyme.FORM_AJOUT_LI.before(nouveauChampLi);
    nouveauChampLi.show('slow');
};
