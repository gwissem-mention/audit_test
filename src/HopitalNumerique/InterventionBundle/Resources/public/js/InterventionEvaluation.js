/**
 * Gestion des évaluations des demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
var HopitalNumeriqueInterventionBundle_InterventionDemande_InterventionEvaluation = function() {};


$(document).ready(function() {
    HopitalNumeriqueInterventionBundle_InterventionDemande_InterventionEvaluation.init();
});


/**
 * Initialise le fonctionnement des évaluations.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_InterventionEvaluation.init = function()
{
    HopitalNumeriqueInterventionBundle_InterventionDemande_InterventionEvaluationEvenement.init();
};



/**
 * Envoie une relance par courriel pour le remplissage du formulaire d'évaluation.
 * 
 * @param integer interventionDemandeId L'ID de la demande d'intervention
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_InterventionEvaluation.relance = function(interventionDemandeId)
{
    alert(interventionDemandeId);
};
