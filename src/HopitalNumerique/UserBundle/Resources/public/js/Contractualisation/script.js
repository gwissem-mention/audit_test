$(document).ready(function() { 
	
	//Id en dur dans la table de références générales
	var ID_DOCUMENT_CONTRACTUALISATION_TYPE_AUTRES = $('#hopitalnumerique_user_contractualisation_typeAutres').val();
	
	//Pour l'edition, gère l'affichage de la date de renouvellement
	gestionAffichageDateRenouvellement(ID_DOCUMENT_CONTRACTUALISATION_TYPE_AUTRES);
	
	//Ajout du champs date de renouvellement si le type de document est "Autres"
    $('#hopitalnumerique_user_contractualisation_typeDocument').on('change', function() 
    {
        	gestionAffichageDateRenouvellement(ID_DOCUMENT_CONTRACTUALISATION_TYPE_AUTRES);
    });
    
    //Gestion du bouton delete : changement du fichier uploadé
    $('.deleteUploadedFile').on('click',function(){
        $('.uploadedFile, .deleteUploadedFile ').hide();
        $('.uploadedFile').html('');
        $('.inputUpload').show();
        $('#hopitalnumerique_user_contractualisation_path').val('');
        $('#hopitalnumerique_user_contractualisation_file').val('');
    });
	
});

function gestionAffichageDateRenouvellement(ID_DOCUMENT_CONTRACTUALISATION_TYPE_AUTRES)
{
	if(ID_DOCUMENT_CONTRACTUALISATION_TYPE_AUTRES != $('#hopitalnumerique_user_contractualisation_typeDocument').val())
	{
		//Récupération du form group pour la date de renouvellement 
		$('#hopitalnumerique_user_contractualisation_dateRenouvellement').parent().parent().removeClass('hide');
	}
	else
	{
		//Récupération du form group pour la date de renouvellement 
		$('#hopitalnumerique_user_contractualisation_dateRenouvellement').parent().parent().addClass('hide');	
		$('#hopitalnumerique_user_contractualisation_dateRenouvellement').val('');	
	}
}