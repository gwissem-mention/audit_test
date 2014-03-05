/**
 * Gestion du formulaire d'une demande d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
var HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire = function() {};

$(document).ready(function() {
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.init();
});

/**
 * Initialisation du formulaire de demande d'intervention.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.init = function()
{
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.initListeDepartements();
    HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.init();
};

/**
 * Initialise la liste des départements.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.initListeDepartements = function()
{
    var regionId = parseInt($('select#hopitalnumerique_interventionbundle_user_region option:selected').attr('value'));
    var departementSelect = $('select#hopitalnumerique_interventionbundle_user_departement');
    $(departementSelect).html('');

    $.getJSON(
        '/compte-hn/intervention/reference/departements/json',
        {
            region:regionId
        },
        function(departements)
        {
            var departementSelectHtml = '';
            $.each(departements, function(index, departement) {
                departementSelectHtml += '<option value="' + departement.id + '">' + departement.libelle + '</option>';
            });
            $(departementSelect).html(departementSelectHtml);
        }
    );
};
