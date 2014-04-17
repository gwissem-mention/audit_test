$(document).ready(function() { 
	
	//Charge la date de fin d'inscription en fonction de la date de la session
    $('#hopitalnumerique_module_session_dateSession').on('change', function() 
    {
    	//Si la date de fin d'inscription n'est pas renseigné uniquement
    	if('' == $('#hopitalnumerique_module_session_dateFermetureInscription').val())
		{
    		$('#hopitalnumerique_module_session_dateFermetureInscription').val($('#hopitalnumerique_module_session_dateSession').val());
		}
    	//Sinon on demande à l'utilisateur si il veut impacter la date de fin d'inscription
    	else if($('#hopitalnumerique_module_session_dateFermetureInscription').val() !== $('#hopitalnumerique_module_session_dateSession').val())
		{
    		apprise('Voulez-vous aussi modifier la date de fermeture des inscriptions?', { verify:true, textYes:'Oui', textNo:'Non' }, function(reponse)
		    {
		        if (reponse)
	        	{
    	    		$('#hopitalnumerique_module_session_dateFermetureInscription').val($('#hopitalnumerique_module_session_dateSession').val());
	        	}
		    });
		}
    });
    
});