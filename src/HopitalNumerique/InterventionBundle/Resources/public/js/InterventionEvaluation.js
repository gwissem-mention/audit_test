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
    HopitalNumeriqueInterventionBundle_InterventionDemande_InterventionEvaluation.initChamps();
};

/**
 * Initialise les champs du formulaire d'initialisation des évaluations.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_InterventionEvaluation.initChamps = function()
{
    $('input#nodevo_questionnaire_questionnaire_date_23_evaluation_intervention_date').datepicker({
        dateFormat:'yy-mm-dd'
    });
};
/**
 * Initialise les champs du formulaire d'initialisation des évaluations.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_InterventionEvaluation.setAutresEtablissements = function(autresEtablissements)
{
    var autresEtablissementsChamp = $('textarea#nodevo_questionnaire_questionnaire_textarea_25_evaluation_autres_etablissements');
    $(autresEtablissementsChamp).val('');
    
    for (var i in autresEtablissements)
    {
        $(autresEtablissementsChamp).val($(autresEtablissementsChamp).val() + autresEtablissements[i]);
        if (i < $(autresEtablissements).size() - 1)
            $(autresEtablissementsChamp).val($(autresEtablissementsChamp).val() + "\n");
    }
};


/**
 * Envoie une relance par courriel pour le remplissage du formulaire d'évaluation.
 * 
 * @param integer interventionDemandeId L'ID de la demande d'intervention
 * @return boolean FAUX pour ne pas que la page avec le grid se recharge
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_InterventionEvaluation.relance = function(interventionDemandeId)
{
    apprise('Confirmez-vous l\'envoi d\'une relance pour cette demande d\'intervention ?', { verify:true, textYes:'Oui', textNo:'Non' }, function(reponse)
    {
        if (reponse)
        {
            var evaluationEnvoiRelanceUrl = '/compte-hn/intervention/demande/' + interventionDemandeId + '/evaluation/relance';

            $.ajax(evaluationEnvoiRelanceUrl, {
                success:function() {
                    apprise('La relance a été envoyée, merci !');
                    return false;
                }
            });
        }
    });

    return false;
};
