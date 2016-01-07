/**
 * Classe gérant la recherche des membres de la communauté de pratiques.
 */
var CommunautePratique_User_Recherche = function() {};

$(document).ready(function() {
    CommunautePratique_User_Recherche.init();
});

/**
 * Initialisation.
 */
CommunautePratique_User_Recherche.init = function()
{
    CommunautePratique_User_Recherche.initChamps();
};

/**
 * Initialise les champs de recherche.
 */
CommunautePratique_User_Recherche.initChamps = function()
{
    $('#hopitalnumerique_communautepratiquebundle_user_recherche_profilEtablissementSante').multiselect({
        nonSelectedText: 'Filtrer par profils',
        buttonContainer: '<div class="btn-group" />',
        numberDisplayed: 1,
        buttonWidth: '100%',
        nSelectedText: 'profils sélectionnés',
        allSelectedText: 'Tous'
    });

    $('#hopitalnumerique_communautepratiquebundle_user_recherche_region').multiselect({
        nonSelectedText: 'Filtrer par régions',
        buttonContainer: '<div class="btn-group" />',
        numberDisplayed: 1,
        buttonWidth: '100%',
        nSelectedText: 'régions sélectionnées',
        allSelectedText: 'Tous'
    });

    $('#hopitalnumerique_communautepratiquebundle_user_recherche_statutEtablissementSante').multiselect({
        nonSelectedText: 'Filtrer par types d\'établissement',
        buttonContainer: '<div class="btn-group" />',
        numberDisplayed: 1,
        buttonWidth: '100%',
        nSelectedText: 'types sélectionnés',
        allSelectedText: 'Tous'
    });

    $('#hopitalnumerique_communautepratiquebundle_user_recherche_typeActivite').multiselect({
        nonSelectedText: 'Filtrer par types d\'activité',
        buttonContainer: '<div class="btn-group" />',
        numberDisplayed: 1,
        buttonWidth: '100%',
        nSelectedText: 'types sélectionnés',
        allSelectedText: 'Tous'
    });
};
