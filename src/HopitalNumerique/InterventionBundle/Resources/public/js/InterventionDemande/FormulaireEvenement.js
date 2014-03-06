/**
 * Gestion des événements du formulaire d'une demande d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
var HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement = function() {};

/**
 * Initialisation du formulaire de demande d'intervention.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.init = function()
{
    HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initRegion_Change();
    HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initDepartement_Change();
};

/**
 * Initialisation de l'événement d'un changement de région.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initRegion_Change = function()
{
    var regionSelect = $('select#hopitalnumerique_interventionbundle_user_region');

    $(regionSelect).change(function() {
        HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.initListeDepartements();
    });
};
/**
 * Initialisation de l'événement d'un changement de département.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initDepartement_Change = function()
{
    var departementSelect = $('select#hopitalnumerique_interventionbundle_user_departement');

    $(departementSelect).change(function() {
        HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.initListeEtablissements();
    });
};