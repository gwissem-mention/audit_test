var loader;

$(document).ready(function() { 
    loader = $('#contact_form').nodevoLoader();
    var idDepartement = 0;
    
    // ------- Gestion des listes déroulantes en AJAX ----------
    
    //Récupération de l'id du département si il on est en édition
    if(null !== $('#hopital_numerique_contact_contact_departement').val())
        idDepartement = $('#hopital_numerique_contact_contact_departement').val();

    // --- Département
    
    //Chargement des départements du formulaire en fonction de la région selectionnée
    chargementDepartement();

    //Si le département était renseigné on le recharge une fois que la liste des département est correct
    if( 0 != idDepartement )
        $('#hopital_numerique_contact_contact_departement').val(idDepartement);
    
    //Ajout de la fonction de chargement des départements sur le on change des régions
    $('#hopital_numerique_contact_contact_region').on('change', function() 
    {
        chargementDepartement();
    });
    
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

/**
 * Permet de charger les départements en fonction de la région selectionné en ajax
 */
function chargementDepartement(){
    loader.start();

    $.ajax({
        url  : $('#departement-url').val(),
        data : {
            id : $('#hopital_numerique_contact_contact_region').val(),
        },
        type    : 'POST',
        async   : false,
        success : function( data ){
            $('#hopital_numerique_contact_contact_departement').html( data );
            loader.finished();
        }
    });
}