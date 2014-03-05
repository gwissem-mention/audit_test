$(document).ready(function() { 

	//Ajout de la fonction de chargement des entreprises sur le on change des départements
    $('#nodevo_user_user_statutEtablissementSante').on('change', function() 
    {
    	viderAutreStructure();
    });
    //Ajout de la fonction de chargement des entreprises sur le on change des départements
    $('#nodevo_user_user_etablissementRattachementSante').on('change', function() 
    {
    	viderAutreStructure();
    });
    //Ajout de la fonction de chargement des entreprises sur le on change des départements
    $('#nodevo_user_user_autreStructureRattacheementSante').on('change', function() 
    {
    	viderAutreStructure();
    });
    //Ajout de la fonction de chargement des entreprises sur le on change des départements
    $('#nodevo_user_user_fonctionEtablissementSante').on('change', function() 
    {
    	viderAutreStructure();
    });
    //Ajout de la fonction de chargement des entreprises sur le on change des départements
    $('#nodevo_user_user_raisonInscriptionSante').on('change', function() 
    {
    	viderAutreStructure();
    });
    
    
    //Ajout de la fonction de chargement des entreprises sur le on change des départements
    $('#nodevo_user_user_nomStructure').on('change', function() 
    {
    	viderStructureEtablissementSante();
    });
    //Ajout de la fonction de chargement des entreprises sur le on change des départements
    $('#nodevo_user_user_fonctionStructure').on('change', function() 
    {
    	viderStructureEtablissementSante();
    });
    //Ajout de la fonction de chargement des entreprises sur le on change des départements
    $('#nodevo_user_user_raisonInscriptionStructure').on('change', function() 
    {
    	viderStructureEtablissementSante();
    });
    
});

//
function viderStructureEtablissementSante()
{	
	//Récupération de tout les champs dans établissement de santé
	var childsEtablissementSante = $("#etablissement_sante input,#etablissement_sante select");
	
	childsEtablissementSante.each(function(){
		$(this).val('');
	})
}

function viderAutreStructure()
{
	//Récupération de tout les champs dans établissement de santé
	var childsAutreStructure = $("#autre_etablissement_sante input,#autre_etablissement_sante select");
	
	childsAutreStructure.each(function(){
		$(this).val('');
	})
}