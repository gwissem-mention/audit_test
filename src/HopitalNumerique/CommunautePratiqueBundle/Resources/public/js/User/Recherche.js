/**
 * Classe gérant la recherche des membres de la communauté de pratique.
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
    $('#hopitalnumerique_communautepratiquebundle_user_recherche_profileType').multiselect({
        nonSelectedText: 'Profils',
        buttonContainer: '<div class="btn-group" />',
        numberDisplayed: 1,
        buttonWidth: '100%',
        nSelectedText: 'profils sélectionnés',
        allSelectedText: 'Tous'
    });

    $('#hopitalnumerique_communautepratiquebundle_user_recherche_region').multiselect({
        nonSelectedText: 'Régions',
        buttonContainer: '<div class="btn-group" />',
        numberDisplayed: 1,
        buttonWidth: '100%',
        nSelectedText: 'régions sélectionnées',
        allSelectedText: 'Tous'
    });

    $('#hopitalnumerique_communautepratiquebundle_user_recherche_organizationType').multiselect({
        nonSelectedText: 'Types d\'établissement',
        buttonContainer: '<div class="btn-group" />',
        numberDisplayed: 1,
        buttonWidth: '100%',
        nSelectedText: 'types sélectionnés',
        allSelectedText: 'Tous'
    });

    $('#hopitalnumerique_communautepratiquebundle_user_recherche_activities').multiselect({
        nonSelectedText: 'Types d\'activité',
        buttonContainer: '<div class="btn-group" />',
        numberDisplayed: 1,
        buttonWidth: '100%',
        nSelectedText: 'types sélectionnés',
        allSelectedText: 'Tous'
    });
};
