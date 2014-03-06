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
    HopitalNumeriqueInterventionBundle_InterventionDemande_FormulaireEvenement.init();
};

/**
 * Met à jour la liste des départements.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.majListeDepartements = function()
{
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire._videChampDepartements();
    var regionId = parseInt($('select#hopitalnumerique_interventionbundle_user_region option:selected').attr('value'));
    
    $.getJSON(
        '/compte-hn/intervention/reference/departements/json',
        {
            region:regionId
        },
        function(departements)
        {
            HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire._majChampDepartements(departements)
        }
    );
};
/**
 * Vide le SELECT des départements.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire._videChampDepartements = function()
{
    var departementSelect = $('select#hopitalnumerique_interventionbundle_user_departement');
    $(departementSelect).html('');
};
/**
 * Raffraîchit le SELECT des départements.
 * 
 * @param array departements Liste des départements à afficher.
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire._majChampDepartements = function(departements)
{
    var departementSelect = $('select#hopitalnumerique_interventionbundle_user_departement');
    var departementSelectHtml = '';
    
    $.each(departements, function(index, departement) {
        departementSelectHtml += '<option value="' + departement.id + '">' + departement.libelle + '</option>';
    });
    
    $(departementSelect).html(departementSelectHtml);
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.initListeEtablissements();
};

/**
 * Initialise la liste des établissements de santé de rattachement.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire.initListeEtablissements = function()
{
    HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire._videChampEtablissements();
    var departementId = parseInt($('select#hopitalnumerique_interventionbundle_user_departement option:selected').attr('value'));
    
    if (departementId != undefined)
    {
        $.getJSON(
            '/compte-hn/intervention/etablissement/etablissements/json',
            {
                departement:departementId
            },
            function(etablissementsRegroupesParTypeOrganisme)
            {
                HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire._majChampEtablissements(etablissementsRegroupesParTypeOrganisme);
            }
        );
    }
};
/**
 * Vide le SELECT des établissements.
 * 
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire._videChampEtablissements = function()
{
    var etablissementSelect = $('select#hopitalnumerique_interventionbundle_user_etablissementRattachementSante');
    $(etablissementSelect).html('');
};
/**
 * Raffraîchit le SELECT des établissements.
 * 
 * @param array etablissementsRegroupesParTypeOrganisme Liste des établissements regroupés par type d'organisme à afficher.
 * @return void
 */
HopitalNumeriqueInterventionBundle_InterventionDemande_Formulaire._majChampEtablissements = function(etablissementsRegroupesParTypeOrganisme)
{
    var etablissementSelect = $('select#hopitalnumerique_interventionbundle_user_etablissementRattachementSante');
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
