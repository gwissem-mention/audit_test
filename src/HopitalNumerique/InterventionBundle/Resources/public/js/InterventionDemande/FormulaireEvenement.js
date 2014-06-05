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
    HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initBoutonSoumission_Click();
    HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initFormulaireCreation_Submit();
    HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initFormulaireEdition_Submit();
    HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initRegion_Change();
    HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initAutresEtablissements_Change();
    HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initInterventionEtat_Click();
    HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initAmbassadeur_Change();
    HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initInterventionRegroupement_Click();
};

/**
 * Initialise l'événement du clic sur un bouton pour envoyer le questionnaire.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initBoutonSoumission_Click = function()
{
    var FormulaireBoutonSoumission = $('[data-form-submit]');

    $(FormulaireBoutonSoumission).click(function() {
        if ($(this).attr('data-form-submit').indexOf('ferme') > -1)
            $('#do').val('save-close');
        
        if ($('form#form_intervention_demande_nouveau').size() > 0)
            $('form#form_intervention_demande_nouveau').submit();
        else if ($('form#form_intervention_demande_edition').size() > 0)
            $('form#form_intervention_demande_edition').submit();
    });
};

/**
 * Initialise l'événement de soumission du formulaire de création d'une demande d'intervention.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initFormulaireCreation_Submit = function()
{
    var demandeCreationFormulaire = $('form#form_intervention_demande_nouveau');

    $(demandeCreationFormulaire).submit(function() {
        return HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.verifieFormulaire();
    });
};
/**
 * Initialise l'événement de soumission du formulaire d'édition d'une demande d'intervention.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initFormulaireEdition_Submit = function()
{
    var demandeEditionFormulaire = $('form#form_intervention_demande_edition');

    $(demandeEditionFormulaire).submit(function() {
        return HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.verifieFormulaire();
    });
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
        HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majListeAmbassadeurs();
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

/**
 * Initialisation de l'événement d'un clic sur un bouton de modification de l'état de l'intervention en cours.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initInterventionEtat_Click = function()
{
    var boutonModificationInterventionEtatRefus = $('button[data-intervention-etat-refus]');
    var boutonModificationInterventionEtatAnnulation = $('button[data-intervention-etat-annulation]');
    var autresBoutonsModificationInterventionEtat = $('button[data-intervention-etat]');
    
    $(boutonModificationInterventionEtatRefus).click(function() {
        HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majInterventionEtatRefus(parseInt($(this).attr('data-intervention-etat-refus')));
    });
    $(boutonModificationInterventionEtatAnnulation).click(function() {
        HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majInterventionEtatAnnulation(parseInt($(this).attr('data-intervention-etat-annulation')));
    });
    $(autresBoutonsModificationInterventionEtat).click(function() {
        HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.enregistreInterventionEtat(parseInt($(this).attr('data-intervention-etat')));
    });
};


/**
 * Initialisation de l'événement d'un changement d'ambassadeur.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initAmbassadeur_Change = function()
{
    //<-- Transfert d'un ambassadeur
    var ambassadeursSelect = $('select#intervention_demande_ambassadeur_change');
    $(ambassadeursSelect).change(function() {
        HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majActivationTransfertAmbassadeur();
    });
    //-->
    
    //<-- Changement d'ambassadeur dans le formulaire
    var ambassadeurSelect = $('select.hopitalnumerique_interventionbundle_interventiondemande_ambassadeur');
    $(ambassadeurSelect).change(function() {
        HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majListeObjets();
    });
    //-->
};


/**
 * Initialisation de l'événement d'un clic sur un bouton de regroupement d'une intervention.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.initInterventionRegroupement_Click = function()
{
    var interventionsRegroupementsBoutons = $('button[data-intervention-similaire]');

    $(interventionsRegroupementsBoutons).click(function() {
        HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.regroupeInterventionSimilaire(parseInt($(this).attr('data-intervention-similaire')), parseInt($(this).attr('data-intervention-regroupement-type')));
    });
};
