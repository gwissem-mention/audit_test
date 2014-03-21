/**
 * Gestion du formulaire de création d'une évaluation de demande d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
var HopitalNumeriqueInterventionBundle_InterventionEvaluationFormulaireCreation = function() {};


$(document).ready(function() {
    HopitalNumeriqueInterventionBundle_InterventionEvaluationFormulaireCreation.init();
});


/**
 * Initialise le fonctionnement du formulaire de création d'une évaluation de demande d'intervention.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionEvaluationFormulaireCreation.init = function()
{
    HopitalNumeriqueInterventionBundle_InterventionEvaluationFormulaireCreation.initChamps();
};

/**
 * Initialise les champs du formulaire de création d'une évaluation de demande d'intervention.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionEvaluationFormulaireCreation.initChamps = function()
{
    var interventionDateChamp = $('input#nodevo_questionnaire_questionnaire_date_23_evaluation_intervention_date');
    var aujourdhui = new Date();
    
    $(interventionDateChamp).val(aujourdhui.toISOString().slice(0, 10));
};