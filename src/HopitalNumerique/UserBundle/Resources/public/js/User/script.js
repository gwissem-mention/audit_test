$(document).ready(function() { 

    var idEntreprise = 0;
    var idDepartement = 0;
    
    $("#nodevo_user_user_roles").select2({ 
        maximumSelectionSize: 1,
        formatSelectionTooBig : function(maxSize){
            return 'Vous ne pouvez associer qu\'un seul groupe par utilisateur';
        }
    });
    
    //Récupération de l'id du département si il on est en édition
    if(null !== $('#nodevo_user_user_departement').val())
    	idDepartement = $('#nodevo_user_user_departement').val();
    //Récupération de l'id de l'entreprise si il on est en édition
    if(null !== $('#nodevo_user_user_etablissementRattachementSante').val())
    	idEntreprise = $('#nodevo_user_user_etablissementRattachementSante').val();

    //Chargement des départements du formulaire en fonction de la région selectionnée
	chargementDepartement();

    //Si le département était renseigné on le recharge une fois que la liste des département est correct
    if( 0 != idDepartement )
    	$('#nodevo_user_user_departement').val(idDepartement);
	
	//Ajout de la fonction de chargement des départements sur le on change des régions
    $('#nodevo_user_user_region').on('change', function() 
    {
    	chargementDepartement();
    	chargementEntreprise();
    });
    
    //Chargement des entreprises du formulaire en fonction du département selectionné
	chargementEntreprise();
	
	//Si le département était renseigné on le recharge une fois que la liste des département est correct
    if( 0 != idEntreprise )
    	$('#nodevo_user_user_etablissementRattachementSante').val(idEntreprise);

	//Ajout de la fonction de chargement des entreprises sur le on change des départements
    $('#nodevo_user_user_departement').on('change', function() 
    {
    	chargementEntreprise();
    });
    
    //Chargement des masks du formulaire
    chargementMaskFormulaire();
    
    //Ouerture de l'onglet ayant des données : si l'un des champs de structure n'est pas vide on ouvre l'onglet
    if('' != $('#nodevo_user_user_nomStructure').val()
    	|| '' != $('#nodevo_user_user_fonctionStructure').val())
	{
    	$('#autre_etablissement_sante_collapse').click();
	}
    
});

/**
 * Permet de charger les départements en fonction de la région selectionné en ajax
 */
function chargementDepartement(){
	var loader = $('#form_edit_user').nodevoLoader().start();

    $.ajax({
        url  : $('#departement-url').val(),
        data : {
            id : $('#nodevo_user_user_region').val(),
        },
        type    : 'POST',
        async   : false,
        success : function( data ){
            $('#nodevo_user_user_departement').html( data );
            loader.finished();
        }
    });
}

/**
 * Permet de charger les entreprises en fonction du département selectionné en ajax
 */
function chargementEntreprise(){
	var loader = $('#form_edit_user').nodevoLoader().start();

    $.ajax({
        url  : $('#etablissement-url').val(),
        data : {
            id : $('#nodevo_user_user_departement').val(),
        },
        type    : 'POST',
        async   : false,
        success : function( data ){
            $('#nodevo_user_user_etablissementRattachementSante').html( data );
            loader.finished();
        }
    });
}

/**
 * Chargement des différents mask sur les inputs
 */
function chargementMaskFormulaire()
{    
    //Mask
    $("#nodevo_user_user_telephonePortable").mask($("#nodevo_user_user_telephonePortable").data("mask"));
    $("#nodevo_user_user_telephoneDirect").mask($("#nodevo_user_user_telephoneDirect").data("mask"));	
}

/**
 * Fonction appelée lors du click sur le bouton save
 */
function sauvegardeFormulaire()
{
	//Récupération de tout les champs dans établissement de santé
	var childsEtablissementSante = $("#etablissement_sante input,#etablissement_sante select");
	
	//Si l'onglet est caché, on vide tous ses inputs
	if('collapsed' == $("#etablissement_sante_collapse").attr('class'))
	{
		childsEtablissementSante.each(function(){
			$(this).val('');
		})
	}
	
	//Récupération de tout les champs dans établissement de santé
	var childsAutreEtablissementSante = $("#autre_etablissement_sante input,#autre_etablissement_sante select");

	if('collapsed' == $("#autre_etablissement_sante_collapse").attr('class'))
	{
		childsAutreEtablissementSante.each(function(){
			$(this).val('');
		})
	}
	
	$('form').submit();
}