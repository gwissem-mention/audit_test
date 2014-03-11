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
    HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initAutresEtablissements_Change();
};

/**
 * Initialisation de l'événement d'un changement de région.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initRegion_Change = function()
{
    var regionSelect = $('select.hopitalnumerique_interventionbundle_interventiondemande_region');

    $(regionSelect).change(function() {
        HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majListeAutresEtablissements();
    });
};
/**
 * Initialisation de l'événement d'un changement parmi les autres établissements de santé.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initAutresEtablissements_Change = function()
{
    var etablissementsSelect = $('select.hopitalnumerique_interventionbundle_interventiondemande_etablissements');

    $(etablissementsSelect).change(function() {
        HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majListeReferents();
    });
};
