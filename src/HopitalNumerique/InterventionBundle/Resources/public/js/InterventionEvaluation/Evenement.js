/**
 * Gestion des événements pour les évaluations des demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
var HopitalNumeriqueInterventionBundle_InterventionDemande_InterventionEvaluationEvenement = function() {};


/**
 * Initialise les événements pour les évaluations.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_InterventionEvaluationEvenement.init = function()
{
    HopitalNumeriqueInterventionBundle_InterventionDemande_InterventionEvaluationEvenement.initRelance_click();
};


/**
 * Initialise l'événement de l'envoi d'une relance.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_InterventionEvaluationEvenement.initRelance_click = function()
{
    var relanceBoutons = $('button[data-evaluation-demande]');
    
    relanceBoutons.click(function() {
        var interventionDemandeId = parseInt($(this).attr('data-evaluation-demande'));
        return HopitalNumeriqueInterventionBundle_InterventionDemande_InterventionEvaluation.relance(interventionDemandeId);
    });
};
