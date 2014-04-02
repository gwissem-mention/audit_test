$(document).ready(function() { 

	//Chargement des masks du formulaire
    chargementMaskFormulaire();
});

/**
 * Chargement des différents mask sur les inputs
 */
function chargementMaskFormulaire()
{    
    //Mask
    $("#hopital_numerique_contact_contact_telephone").mask($("#hopital_numerique_contact_contact_telephone").data("mask")); 
}