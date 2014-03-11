jQuery(document).ready(function() { 

	var toggleEtablissementAutre = '';
	
	//Ajout de la fonction de chargement des entreprises sur le on change des départements
    $('.etablissement_sante').on('focus', function() 
    {
    	if('' == toggleEtablissementAutre)
    		toggleEtablissementAutre = 'etablissement';
    	else if('autre' == toggleEtablissementAutre)
		{
    		toggleEtablissementAutre = viderAutreStructure($(this));
		}
    });   
    
    //Ajout de la fonction de chargement des entreprises sur le on change des départements
    $('.autre_structure').on('focus', function() 
    {
console.log(toggleEtablissementAutre);
    	
    	if('' == toggleEtablissementAutre)
    		toggleEtablissementAutre = 'autre';
    	else if('etablissement' == toggleEtablissementAutre)
		{
    		toggleEtablissementAutre = viderStructureEtablissementSante($(this));
		}
    });
    
});

function viderStructureEtablissementSante(elementCourant)
{		
	var textRetour = '';  
	
	apprise("Vous-êtes sur le point de supprimer les données de 'Etablissement de santé', continuer ?", {'verify':true,'textYes':'Oui','textNo':'Non'}, function(r) {
		if(r)
		{				
			//Récupération de tout les champs dans établissement de santé
			var childsEtablissementSante = $("#etablissement_sante input,#etablissement_sante select");
			
			childsEtablissementSante.each(function(){
				$(this).val('');
			})	
			$('#s2id_nodevo_user_user_etablissementRattachementSante').select2('data', null);

			textRetour = 'autre';
		}
		else
		{
			elementCourant.val('');

			textRetour = 'etablissement';
		}
    });
	
	console.log(textRetour);
	
	return textRetour;
}

function viderAutreStructure(elementCourant)
{
	var textRetour = '';
	
	var test = confirm('Oui/Non ?');
	
	if(test)
	{
		//Récupération de tout les champs dans établissement de santé
		var childsAutreStructure = $("#autre_etablissement_sante input,#autre_etablissement_sante select");
		
		childsAutreStructure.each(function(){
			$(this).val('');
		})	
		
		textRetour = 'etablissement';
	}
	else
	{
		elementCourant.val('');
		
		textRetour = 'autre';
	}
	
//	apprise("Vous-êtes sur le point de supprimer les données de 'Autre structure', continuer ?", {'verify':true,'textYes':'Oui','textNo':'Non'}, function(r) {
//		if(r)
//		{
//			//Récupération de tout les champs dans établissement de santé
//			var childsAutreStructure = $("#autre_etablissement_sante input,#autre_etablissement_sante select");
//			
//			childsAutreStructure.each(function(){
//				$(this).val('');
//			})	
//			
//			textRetour = 'etablissement';
//		}
//		else
//		{
//			elementCourant.val('');
//			
//			textRetour = 'autre';
//		}
//    });
	
	return textRetour;	
}