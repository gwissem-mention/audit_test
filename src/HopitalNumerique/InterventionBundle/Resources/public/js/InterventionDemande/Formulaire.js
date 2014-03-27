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
 * @var array Les régions qui ont été choisies : id => libellé
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.REGIONS_CHOISIES = new Array();

/**
 * Initialisation du formulaire de demande d'intervention.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.init = function()
{
    HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.init();
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.initChamps();
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majListeAutresEtablissements();
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majActivationTransfertAmbassadeur();
};

/**
 * Initialise les champs du formulaire.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.initChamps = function()
{
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.videChampAutresEtablissements();
    $('select[multiple]').select2({
        formatNoMatches:function()
        {
            return 'Aucune option disponible'
        }
    });
};


/**
 * Cache les boutons d'action.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.cacheBoutonsAction = function()
{
    $('div.panel_boutons').css({ visibility:'hidden' });
};


/**
 * Vérifie, avant soumission, le formulaire de création d'une nouvelle demande d'intervention.
 * 
 * @return boolean VRAI ssi le formulaire est valide
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.verifieFormulaire = function()
{
    var formulaireVerification = HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.verifieChampObjets();
    var formulaireVerification = (HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.verifieChampEtablissements() && formulaireVerification);
    
    return formulaireVerification;
};
/**
 * Vérifie, avant soumission, le champ Objets du formulaire de demande d'intervention.
 * 
 * @return boolean VRAI ssi le champ est valide
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.verifieChampObjets = function()
{
    var objetsChamp = $('select.hopitalnumerique_interventionbundle_interventiondemande_objets');
    //var objetsChampEngineValidator = $('div.hopitalnumerique_interventionbundle_interventiondemande_objets ul.select2-choices');
    var objetsChampEngineValidator = $($('div#s2id_' + $('select.hopitalnumerique_interventionbundle_interventiondemande_objets').prop('id')));
    
    if ($(objetsChamp).val() == null)
    {
        $(objetsChampEngineValidator).validationEngine('showPrompt', '* Ce champ est requis', 'red', 'topRight', true);
        return false;
    }
    else $(objetsChampEngineValidator).validationEngine('hide');

    return true;
};
/**
 * Vérifie, avant soumission, le champ Établissements du formulaire de demande d'intervention.
 * 
 * @return boolean VRAI ssi le champ est valide
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.verifieChampEtablissements = function()
{
    // Obligatoire que pour le CMSI
    var objetsChamp = $('select#hopitalnumerique_interventionbundle_interventiondemande_cmsi_etablissements');
    
    if ($(objetsChamp).size() > 0)
    {
        //var objetsChampEngineValidator = $('div.hopitalnumerique_interventionbundle_interventiondemande_etablissements ul.select2-choices');
        var objetsChampEngineValidator = $('div#s2id_hopitalnumerique_interventionbundle_interventiondemande_cmsi_etablissements');
        
        if ($(objetsChamp).val() == null)
        {
            $(objetsChampEngineValidator).validationEngine('showPrompt', '* Ce champ est requis', 'red', 'topRight', true);
            return false;
        }
        else $(objetsChampEngineValidator).validationEngine('hide');
    }

    return true;
};


/**
 * Modifie l'état de la demande d'intervention en refusé.
 * 
 * @param integer interventionEtatId L'ID du nouvel état de la demande d'intervention
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majInterventionEtatRefus = function(interventionEtatId)
{
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.cacheBoutonsAction();
    var loaderAjax = $('#intervention_demande_panel').nodevoLoader().start();
    
    var changementEtatUrl = '/compte-hn/intervention/demande/' + HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.INTERVENTION_DEMANDE_ID + '/etat/' + interventionEtatId + '/change';

    $.ajax(changementEtatUrl, {
        method:'POST',
        data:{
            message:$('textarea#etat_intervention_refus_justification').val()
        },
        success:function() {
            loaderAjax.finished();
            Nodevo_Web.rechargePage();
        }
    });
};
/**
 * Modifie l'état de la demande d'intervention en annulé.
 * 
 * @param integer interventionEtatId L'ID du nouvel état de la demande d'intervention
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majInterventionEtatAnnulation = function(interventionEtatId)
{
    apprise('Confirmez-vous l\'annulation de la demande ?', { verify:true, textYes:'Oui', textNo:'Non' }, function(reponse)
    {
        if (reponse)
            HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.enregistreInterventionEtat(interventionEtatId);
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
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.cacheBoutonsAction();
    var loaderAjax = $('#intervention_demande_panel').nodevoLoader().start();
    
    var changementEtatUrl = '/compte-hn/intervention/demande/' + HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.INTERVENTION_DEMANDE_ID + '/etat/' + interventionEtatId + '/change';
    
    $.ajax(changementEtatUrl, {
        method:'POST',
        success:function() {
            loaderAjax.finished();
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
 * Retourne le nom de la région des établissements.
 * 
 * @return string Le nom de la région sélectionnée
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.getRegionNom = function()
{
    var regionOptionSelectionnee = $('select.hopitalnumerique_interventionbundle_interventiondemande_region option:selected');
    
    return $(regionOptionSelectionnee).html();
}
/**
 * Initialise les régions des établissements choisis.
 * 
 * @param integer[] regionsIds Les IDs des régions des établissements choisis
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.initRegions = function(regionsIds)
{
    $(document).ready(function() {
        $.each(regionsIds, function(index, regionId) {
            HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.setRegion(regionId);
            HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majListeAutresEtablissements();
        });
        HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.initAutresEtablissementsInitiaux();
    });
}
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
    if (HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.listeRegionsExiste())
    {
        var regionId = parseInt($('select.hopitalnumerique_interventionbundle_interventiondemande_region option:selected').attr('value'));

        if (regionId != 0 && HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.REGIONS_CHOISIES[regionId] == undefined)
        {
            var loaderAjax = $('#intervention_demande_panel').nodevoLoader().start();
            $.ajax({
                url:'/compte-hn/intervention/etablissement/etablissements/json',
                dataType:'json',
                async:false,
                data:{
                    region:regionId
                },
                success:function(etablissements) {
                    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.REGIONS_CHOISIES[regionId] = HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.getRegionNom();
                    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majChampAutresEtablissements(etablissements);
                }
            });
            loaderAjax.finished();
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
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majChampAutresEtablissements = function(etablissements)
{
    var etablissementSelect = $('select.hopitalnumerique_interventionbundle_interventiondemande_etablissements');
    var etablissementsSelectHtml = $('select.hopitalnumerique_interventionbundle_interventiondemande_etablissements').html();
    var etablissementsSelectionnesIds = $(etablissementSelect).select2('val');

    etablissementsSelectHtml += '<optgroup label="' + HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.getRegionNom() + '">';
    $.each(etablissements, function(index, etablissement) {
        etablissementsSelectHtml += '<option value="' + etablissement.id + '">' + etablissement.appellation + '</option>';
    });
    etablissementsSelectHtml += '</optgroup>';


    $(etablissementSelect).html(etablissementsSelectHtml);
    $(etablissementSelect).select2('val', etablissementsSelectionnesIds);
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
        /*for (var i = 0; i < HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.AUTRES_ETABLISSEMENTS_INITIAUX_IDS.length; i++)
            $(etablissementSelect).find('option[value=' + HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.AUTRES_ETABLISSEMENTS_INITIAUX_IDS[i] + ']').attr('selected', true);*/
        $('select.hopitalnumerique_interventionbundle_interventiondemande_etablissements').select2('val', HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.AUTRES_ETABLISSEMENTS_INITIAUX_IDS);
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
            var loaderAjax = $('#intervention_demande_panel').nodevoLoader().start();
            
            $.getJSON(
                '/compte-hn/intervention/referents/json',
                {
                    etablissementRattachementSante:autresEtablissementsIds
                },
                function(users)
                {
                    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majChampReferents(users);

                    loaderAjax.finished();
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
 * Active ou désactive le bouton de transfert d'un ambassadeur en fonction de l'ambassadeur choisi.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majActivationTransfertAmbassadeur = function()
{
    var nouvelAmbassadeurOptionSelectionnee = $('select#intervention_demande_ambassadeur_change option:selected');
    if ($(nouvelAmbassadeurOptionSelectionnee).size() > 0)
    {
        var nouvelAmbassadeurId = parseInt($(nouvelAmbassadeurOptionSelectionnee).val());
        
        $('#intervention_demande_ambassadeur_bouton').attr('disabled', (nouvelAmbassadeurId == 0));
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
        apprise('Confirmez-vous le transfert d\'ambassadeur ?', { verify:true, textYes:'Oui', textNo:'Non' }, function(reponse)
        {
            if (reponse)
            {
                HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.cacheBoutonsAction();
                
                var loaderAjax = $('#intervention_demande_panel').nodevoLoader().start();
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
        });
    }
};

/**
 * Regroupe une demande d'intervention.
 * 
 * @param integer interventionRegroupeeId L'ID de l'intervention à regrouper
 * @param integer interventionRegroupementType L'ID du type de regroupement
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.regroupeInterventionSimilaire = function(interventionRegroupeeId, interventionRegroupementType)
{
    apprise('Confirmez-vous ce regroupement ?', { verify:true, textYes:'Oui', textNo:'Non' }, function(reponse)
    {
        if (reponse)
        {
            var interventionRegroupementUrl = '/compte-hn/intervention/demande/' + HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.INTERVENTION_DEMANDE_ID + '/regroupement/' + interventionRegroupementType + '/' + interventionRegroupeeId + '/regroupe';
            
            $.ajax(interventionRegroupementUrl, {
                success:function(reponse) {
                    if (reponse != '1')
                        alert('Le regroupement ne s\est pas fait.');
                    else Nodevo_Web.rechargePage();
                }
            });
        }
    });
}