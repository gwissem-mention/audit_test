/**
 * Gestion du formulaire de création d'une demande d'intervention dans l'admin.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
var HopitalNumeriqueInterventionBundle_InterventionDemande_AdminFormulaireCreation = function() {};

$(document).ready(function() {
    HopitalNumeriqueInterventionBundle_InterventionDemande_AdminFormulaireCreation.init();
});

/**
 * Initialisation du formulaire de création de demande d'intervention de l'admin.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_AdminFormulaireCreation.init = function()
{
    HopitalNumeriqueInterventionBundle_InterventionDemande_AdminFormulaireCreation.initFormulaire_Submit();
};

/**
 * Initialise l'événement de soumission du formulaire de création d'une demande d'intervention dans l'admin.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_AdminFormulaireCreation.initFormulaire_Submit = function()
{
    var demandeCreationFormulaire = $('form#form_intervention_demande_nouveau');

    $(demandeCreationFormulaire).submit(function() {
        return HopitalNumeriqueInterventionBundle_InterventionDemande_AdminFormulaireCreation.verifieFormulaire();
    });
};

/**
 * Vérifie, avant soumission, le formulaire de création d'une nouvelle demande d'intervention.
 * 
 * @return boolean VRAI ssi le formulaire est valide
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_AdminFormulaireCreation.verifieFormulaire = function()
{
    return (
        HopitalNumeriqueInterventionBundle_InterventionDemande_AdminFormulaireCreation.verifieChampAmbassadeur()
        && HopitalNumeriqueInterventionBundle_InterventionDemande_AdminFormulaireCreation.verifieChampDemandeur()
    );
};

/**
 * Vérifie, avant soumission, le champ Ambassadeur du formulaire de demande d'intervention.
 * 
 * @return boolean VRAI ssi le champ est valide
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_AdminFormulaireCreation.verifieChampAmbassadeur = function()
{
    var ambassadeurChamp = $('#hopitalnumerique_interventionbundle_interventiondemande_admin_ambassadeur');
    
    if ($(ambassadeurChamp).size() > 0)
    {
        var ambassadeurChampEngineValidator = $('div#s2id_hopitalnumerique_interventionbundle_interventiondemande_admin_ambassadeur');
        
        if ($(ambassadeurChamp).val() == null || $(ambassadeurChamp).val() == '')
        {
            $(ambassadeurChampEngineValidator).validationEngine('showPrompt', '* Ce champ est requis', 'red', 'topRight', true);
            return false;
        }
        else $(ambassadeurChampEngineValidator).validationEngine('hide');
    }

    return true;
};
/**
 * Vérifie, avant soumission, le champ Demandeur du formulaire de demande d'intervention.
 * 
 * @return boolean VRAI ssi le champ est valide
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_AdminFormulaireCreation.verifieChampDemandeur = function()
{
    var referentChamp = $('#hopitalnumerique_interventionbundle_interventiondemande_admin_referent');
    
    if ($(referentChamp).size() > 0)
    {
        var referentChampEngineValidator = $('div#s2id_hopitalnumerique_interventionbundle_interventiondemande_admin_referent');
        
        if ($(referentChamp).val() == null || $(referentChamp).val() == '')
        {
            $(referentChampEngineValidator).validationEngine('showPrompt', '* Ce champ est requis', 'red', 'topRight', true);
            return false;
        }
        else $(referentChampEngineValidator).validationEngine('hide');
    }

    return true;
};