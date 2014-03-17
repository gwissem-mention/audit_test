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
 * @return boolean FAUX pour ne pas que la page avec le grid se recharge
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_InterventionEvaluation.relance = function(interventionDemandeId)
{
    if (confirm('Confirmez-vous l\'envoi d\'une relance pour cette demande d\'intervention ?'))
    {
        var evaluationEnvoiRelanceUrl = '/compte-hn/intervention/demande/' + interventionDemandeId + '/evaluation/relance';

        $.ajax(evaluationEnvoiRelanceUrl, {
            success:function() {
                alert('La relance a été envoyée, merci !');
                return false;
            }
        });
    }
    
    return false;
};
