$(document).ready(function() {
    //fancybox liste des outils maitrisés
    $('a.link').fancybox({
        'padding'   : 0,
        'autoSize'  : true,
        'scrolling' : 'yes'
    });
});

$(document).bind("carteReady", function(){
    selectedRegion = $('#selected-region').val();

    if ( selectedRegion ){
        $('#canvas_france a').each(function(key, val){
            if( $(this).attr('title') == selectedRegion ){
                $(this).find('path').attr('fill', '#6f3596');
            }
        });
    }
});

/**
 * Fonction permettant de sélectionner l'ensemble des régions de la carte de France
 */
function selectionnerToutesRegions()
{
	//Récupération de l'ensemble des régions
    var regions = ['alsace','aquitaine','auvergne','bassenormandie',
                   'bourgogne','bretagne','centre','corse',
                   'champagneardenne','franchecomte','hautenormandie','iledefrance'
                   ,'languedocroussillon','limousin','lorraine','midipyrenees',
                   'nordpasdecalais','provencealpescotedazur','paysdelaloire','picardie',
                   'poitoucharentes','rhonealpes','guadeloupe','martinique',
                   'martinique','reunion','guyane','mayotte'];
    
    var regionJSON = JSON.stringify(regions);
    $('#selected-region').val(regionJSON);

    //Supprime la carte
    $('#canvas_france').empty();
    
    //la recrée avec toutes les régions supprimées
    afficheCarteFrance(regionJSON, true);
}

/**
 * Fonction permettant de sélectionner l'ensemble des régions de la carte de France
 */
function deselectionnerToutesRegions()
{
    var regions = [];
    
    var regionJSON = JSON.stringify(regions);
    $('#selected-region').val(regionJSON);

    //Supprime la carte
    $('#canvas_france').empty();
    
    //la recrée avec toutes les régions supprimées
    afficheCarteFrance(regionJSON);
}

/*
 * Fonction permettant de mettre à jour la session puis de recharger la page courante
 */
function appliquerRegionsSelectionnees()
{
	$('#btn_appliquer_filtre').addClass('disabled');
	
    $.ajax({
        url  : $('#hopital_numerique_registre_edit_session').val(),
        data : {
        	domaine : $('#domaines_liste').val(),
        	regionJSON : $('#selected-region').val()
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            window.location = data.url;
        }
    });
}