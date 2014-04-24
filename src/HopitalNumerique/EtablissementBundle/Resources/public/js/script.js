jQuery(document).ready(function() {

    //Récupération de l'id du département si il on est en édition
    var idDepartement = 0;  
    if(null !== $('#hopitalnumerique_etablissement_etablissement_departement').val())
	{
    	idDepartement = $('#hopitalnumerique_etablissement_etablissement_departement').val();
	}
	
    //Chargement des départements du formulaire en fonction de la région selectionnée
	chargementDepartement();
	
    $('#hopitalnumerique_etablissement_etablissement_region').on('change', function() {
    	chargementDepartement();
    });

    //Si le département était renseigné on le recharge une fois que la liste des département est correct
    if( 0 != idDepartement )
	{
    	$('#hopitalnumerique_etablissement_etablissement_departement').val(idDepartement);
	}
    
    //bind de Validation Engine
    $('form.toValidate').validationEngine();
});

//Permet de charger les départements en fonction de la région selectionné en ajax
function chargementDepartement(){
	var loader = $('#hopitalnumerique_etablissement_etablissement').nodevoLoader().start();

    $.ajax({
        url  : $('#departement-url').val(),
        data : {
            id : $('#hopitalnumerique_etablissement_etablissement_region').val(),
        },
        type    : 'POST',
        async   : false,
        success : function( data ){
            $('#hopitalnumerique_etablissement_etablissement_departement').html( data );
            loader.finished();
        }
    });
}