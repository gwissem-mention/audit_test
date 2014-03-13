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
 * @var integer L'ID de la demande d'intervention en cours
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.INTERVENTION_DEMANDE_ID = null;
/**
 * @var integer[] Les ID des derniers autres établissements initialement sélectionnés
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.AUTRES_ETABLISSEMENTS_INITIAUX_IDS = new Array();
/**
 * @var integer L'ID du référent initialement sélectionné
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.REFERENT_INITIAL_ID = null;

/**
 * Initialisation du formulaire de demande d'intervention.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.init = function()
{
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majListeAutresEtablissements();
    HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.init();
};


/**
 * Modifie l'état de la demande d'intervention en refusé CMSI.
 * 
 * @param integer interventionEtatId L'ID du nouvel état de la demande d'intervention (refusé CMSI)
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majInterventionEtatRefusCmsi = function(interventionEtatId)
{
    var changementEtatUrl = '/compte-hn/intervention/demande/' + HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.INTERVENTION_DEMANDE_ID + '/etat/' + interventionEtatId + '/change';

    $.ajax(changementEtatUrl, {
        method:'POST',
        data:{
            message:$('textarea#etat_intervention_refus_cmsi_justification').val()
        },
        success:function() {
            Nodevo_Web.rechargePage();
        }
    });
};
/**
 * Enregistre l'état de la demande d'intervention.
 * 
 * @param integer interventionEtatId L'ID du nouvel état de la demande d'intervention
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.enregistreInterventionEtat = function(interventionEtatId)
{
    var changementEtatUrl = '/compte-hn/intervention/demande/' + HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.INTERVENTION_DEMANDE_ID + '/etat/' + interventionEtatId + '/change';

    $.ajax(changementEtatUrl, {
        success:function() {
            Nodevo_Web.rechargePage();
        }
    });
};


/**
 * Retourne si la liste des régions est présente.
 * 
 * @return boolean VRAI ssi la liste des régions est présente
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.listeRegionsExiste = function()
{
    return ($('select.hopitalnumerique_interventionbundle_interventiondemande_region').size() > 0);
};
/**
 * Sélectionne la région des établissements.
 * 
 * @param integer regionId L'ID de la région à sélectionner
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.setRegion = function(regionId)
{
    var regionSelect = $('select.hopitalnumerique_interventionbundle_interventiondemande_region');
    
    $(regionSelect).val(regionId);
}


/**
 * Initialise la liste des autres établissements de santé de rattachement.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majListeAutresEtablissements = function()
{
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.videChampAutresEtablissements();
    
    if (HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.listeRegionsExiste())
    {
        var regionId = parseInt($('select.hopitalnumerique_interventionbundle_interventiondemande_region option:selected').attr('value'));
    
        if (regionId != 0)
        {
            $.getJSON(
                '/compte-hn/intervention/etablissement/etablissements/json',
                {
                    region:regionId
                },
                function(etablissementsRegroupesParTypeOrganisme)
                {
                    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majChampAutresEtablissements(etablissementsRegroupesParTypeOrganisme);
                }
            );
        }
    }
};
/**
 * Vide le SELECT des autres établissements.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.videChampAutresEtablissements = function()
{
    var etablissementSelect = $('select.hopitalnumerique_interventionbundle_interventiondemande_etablissements');
    $(etablissementSelect).html('');
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.videChampReferents();
};
/**
 * Raffraîchit le SELECT des autres établissements.
 * 
 * @param array etablissementsRegroupesParTypeOrganisme Liste des établissements regroupés par type d'organisme à afficher.
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majChampAutresEtablissements = function(etablissementsRegroupesParTypeOrganisme)
{
    var etablissementSelect = $('select.hopitalnumerique_interventionbundle_interventiondemande_etablissements');
    var etablissementsSelectHtml = '';
    
    $.each(etablissementsRegroupesParTypeOrganisme, function(index, etablissementsRegroupes) {
        etablissementsSelectHtml += '<optgroup label="' + etablissementsRegroupes.typeOrganisme.libelle + '">';
            $.each(etablissementsRegroupes.etablissements, function(index, etablissement) {
                etablissementsSelectHtml += '<option value="' + etablissement.id + '">' + etablissement.nom + '</option>';
            });
        etablissementsSelectHtml += '</optgroup>';
    });
    
    $(etablissementSelect).html(etablissementsSelectHtml);
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.initAutresEtablissementsInitiaux();
};
/**
 * Sélectionne les autres établissements de la demande d'intervention à l'entrée de la page.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.initAutresEtablissementsInitiaux = function()
{
    var etablissementSelect = $('select.hopitalnumerique_interventionbundle_interventiondemande_etablissements');

    if (HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.AUTRES_ETABLISSEMENTS_INITIAUX_IDS.length > 0)
    {
        for (var i = 0; i < HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.AUTRES_ETABLISSEMENTS_INITIAUX_IDS.length; i++)
            $(etablissementSelect).find('option[value=' + HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.AUTRES_ETABLISSEMENTS_INITIAUX_IDS[i] + ']').attr('selected', true);
        HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.AUTRES_ETABLISSEMENTS_INITIAUX_IDS = new Array();
        $(etablissementSelect).trigger('change');
    }
};
/**
 * Sélectionne un établissement dans liste des établissements de rattachement.
 * 
 * @param integer etablissementId L'ID de l'établissement à sélectionner
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.addAutreEtablissement = function(etablissementId)
{
    var etablissementSelect = $('select.hopitalnumerique_interventionbundle_interventiondemande_etablissements');
    
    $(etablissementSelect).find('option[value=' + etablissementId + ']').attr('selected', true);
};
/**
 * Sélectionne un établissement dans liste des établissements de rattachement.
 * 
 * @param integer etablissementId L'ID de l'établissement à sélectionner
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.getAutresEtablissementsSelectionnesIds = function()
{
    var etablissementSelect = $('select.hopitalnumerique_interventionbundle_interventiondemande_etablissements');
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.getAutresEtablissementsSelectionnesIds = new Array();
    
    $.each($(etablissementSelect).find(':selected'), function(index, optionSelectionne) {
        HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.getAutresEtablissementsSelectionnesIds.push($(optionSelectionne).val());
    });
};


/**
 * Initialise la liste des référents de la demande.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majListeReferents = function()
{
    if (HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.listeReferentsExiste())
    {
        HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.videChampReferents();
        var autresEtablissementsIds = HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.getAutresEtablissementsIds();
    
        if (autresEtablissementsIds.length > 0)
        {
            $.getJSON(
                '/compte-hn/intervention/users/json',
                {
                    etablissementRattachementSante:autresEtablissementsIds
                },
                function(users)
                {
                    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majChampReferents(users);
                }
            );
        }
    }
};
/**
 * Retourne si la liste des référents est présente.
 * 
 * @return boolean VRAI ssi la liste des référents est présente
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.listeReferentsExiste = function()
{
    return ($('select.hopitalnumerique_interventionbundle_interventiondemande_referent').size() > 0);
};
/**
 * Retourne les ID des autres établissements sélectionnés.
 * 
 * @return integer[] Les ID des autres établissements sélectionnés 
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.getAutresEtablissementsIds = function()
{
    var autresEtablissementsIds = new Array();
    var autresEtablissementsOptionsSelectionnes = $('select.hopitalnumerique_interventionbundle_interventiondemande_etablissements option:selected');
    
    $.each(autresEtablissementsOptionsSelectionnes, function(index, autresEtablissementsOptionSelectionne) {
        autresEtablissementsIds.push(parseInt($(autresEtablissementsOptionSelectionne).attr('value')));
    });
    
    return autresEtablissementsIds;
};
/**
 * Vide le SELECT du référent.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.videChampReferents = function()
{
    var referentSelect = $('select.hopitalnumerique_interventionbundle_interventiondemande_referent');
    $(referentSelect).html('');
};
/**
 * Raffraîchit le SELECT des référents.
 * 
 * @param array users Liste des référents.
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majChampReferents = function(users)
{
    var referentsSelect = $('select.hopitalnumerique_interventionbundle_interventiondemande_referent');
    var referentsSelectHtml = '';
    
    $.each(users, function(index, referent) {
        referentsSelectHtml += '<option value="' + referent.id + '">' + referent.nom + ' ' + referent.prenom + '</option>';
    });
    
    $(referentsSelect).html(referentsSelectHtml);
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.initReferentInitial();
};
/**
 * Sélectionne le référent de la demande d'intervention à l'entrée de la page.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.initReferentInitial = function()
{
    var etablissementSelect = $('select.hopitalnumerique_interventionbundle_interventiondemande_referent');

    if (HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.REFERENT_INITIAL_ID != null)
    {
        $(etablissementSelect).find('option[value=' + HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.REFERENT_INITIAL_ID + ']').attr('selected', true);
        HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.REFERENT_INITIAL_ID = null;
    }
};


/**
 * Change l'ambassadeur de la demande d'intervention.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.changeAmbassadeur = function()
{
    var nouvelAmbassadeurId = parseInt($('select#intervention_demande_ambassadeur_change option:selected').val());

    if (nouvelAmbassadeurId > 0)
    {
        if (confirm('Confirmez-vous le transfert d\'ambassadeur ?'))
        {
            var loaderAjax = $('.panel_form_visu').nodevoLoader().start();
            var changementAmbassadeurUrl = '/compte-hn/intervention/demande/' + HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.INTERVENTION_DEMANDE_ID + '/ambassadeur/' + nouvelAmbassadeurId + '/change'; 
        
            $.ajax(changementAmbassadeurUrl, {
                success:function(reponse) {
                    if (reponse != '1')
                        alert('L\'ambassadeur n\'a pu être modifié.');
                    else Nodevo_Web.redirige('/compte-hn/intervention/demandes/liste');

                    loaderAjax.finished();
                }
            });
        }
    }
};
