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
 * Initialisation du formulaire de demande d'intervention.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.init = function()
{
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majListeDepartements();
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majListeAutresEtablissements();
    HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.init();
};


/**
 * Met à jour la liste des départements.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majListeDepartements = function()
{
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.videChampDepartements();
    var regionId = parseInt($('select.hopitalnumerique_interventionbundle_user_region option:selected').attr('value'));

    $.getJSON(
        '/compte-hn/intervention/reference/departements/json',
        {
            region:regionId
        },
        function(departements)
        {
            HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majChampDepartements(departements)
        }
    );
};
/**
 * Vide le SELECT des départements.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.videChampDepartements = function()
{
    var departementSelect = $('select.hopitalnumerique_interventionbundle_user_departement');
    $(departementSelect).html('');
};
/**
 * Raffraîchit le SELECT des départements.
 * 
 * @param array departements Liste des départements à afficher.
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majChampDepartements = function(departements)
{
    var departementSelect = $('select.hopitalnumerique_interventionbundle_user_departement');
    var departementSelectHtml = '';
    
    $.each(departements, function(index, departement) {
        departementSelectHtml += '<option value="' + departement.id + '">' + departement.libelle + '</option>';
    });
    
    $(departementSelect).html(departementSelectHtml);
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majListeEtablissements();
};


/**
 * Initialise la liste des établissements de santé de rattachement.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majListeEtablissements = function()
{
    if (HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.listeEtablissementsExiste())
    {
        HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.videChampEtablissements();
        var departementId = parseInt($('select.hopitalnumerique_interventionbundle_user_departement option:selected').attr('value'));
    
        if (departementId != 0)
        {
            $.getJSON(
                '/compte-hn/intervention/etablissement/etablissements/json',
                {
                    departement:departementId
                },
                function(etablissementsRegroupesParTypeOrganisme)
                {
                    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majChampEtablissements(etablissementsRegroupesParTypeOrganisme);
                }
            );
        }
    }
};
/**
 * Retourne si la liste des établissements de santé est présente.
 * 
 * @return boolean VRAI ssi la liste des établissements de santé est présente
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.listeEtablissementsExiste = function()
{
    return ($('select.hopitalnumerique_interventionbundle_user_etablissementRattachementSante').size() > 0);
};
/**
 * Vide le SELECT des établissements.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.videChampEtablissements = function()
{
    var etablissementSelect = $('select.hopitalnumerique_interventionbundle_user_etablissementRattachementSante');
    $(etablissementSelect).html('');
};
/**
 * Raffraîchit le SELECT des établissements.
 * 
 * @param array etablissementsRegroupesParTypeOrganisme Liste des établissements regroupés par type d'organisme à afficher.
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majChampEtablissements = function(etablissementsRegroupesParTypeOrganisme)
{
    var etablissementSelect = $('select.hopitalnumerique_interventionbundle_user_etablissementRattachementSante');
    var etablissementsSelectHtml = '';
    
    $.each(etablissementsRegroupesParTypeOrganisme, function(index, etablissementsRegroupes) {
        etablissementsSelectHtml += '<optgroup label="' + etablissementsRegroupes.typeOrganisme.libelle + '">';
            $.each(etablissementsRegroupes.etablissements, function(index, etablissement) {
                etablissementsSelectHtml += '<option value="' + etablissement.id + '">' + etablissement.nom + '</option>';
            });
        etablissementsSelectHtml += '</optgroup>';
    });
    
    $(etablissementSelect).html(etablissementsSelectHtml);
};


/**
 * Initialise la liste des autres établissements de santé de rattachement.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majListeAutresEtablissements = function()
{
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.videChampAutresEtablissements();
    var regionId = parseInt($('select.hopitalnumerique_interventionbundle_user_region option:selected').attr('value'));

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
};
