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
	var confirmation = confirm ("Vous-êtes sur le point de supprimer les données de 'Etablissement de santé', continuer ?")
	
	if(confirmation)
	{
		//Récupération de tout les champs dans établissement de santé
		var childsEtablissementSante = $("#etablissement_sante input,#etablissement_sante select");
		
		childsEtablissementSante.each(function(){
			$(this).val('');
		})	
		
		return 'autre';
	}
	else
	{
		elementCourant.val('');
		
		return 'etablissement';
	}
}

function viderAutreStructure(elementCourant)
{
	var confirmation = confirm ("Vous-êtes sur le point de supprimer les données de 'Autre structure', continuer ?")
	
	if(confirmation)
	{
		//Récupération de tout les champs dans établissement de santé
		var childsAutreStructure = $("#autre_etablissement_sante input,#autre_etablissement_sante select");
		
		childsAutreStructure.each(function(){
			$(this).val('');
		})	
		
		return 'etablissement';
	}
	else
	{
		elementCourant.val('');
		
		return 'autre';
	}
}