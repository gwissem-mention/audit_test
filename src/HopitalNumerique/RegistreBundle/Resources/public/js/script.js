$(document).ready(function() {
    //fancybox liste des outils maitrisés
    $('a.link').fancybox({
        'padding'   : 0,
        'scrolling' : 'auto',
        'width' 	: '600px',
        'height' 	: 'auto'
    });
    $( "#domaines_liste" ).change(function() {
	appliquerRegionsSelectionnees();
    });
});

$(document).bind("carteReady", function(){
    selectedRegion = $('#selected-region').val();

    if ( selectedRegion ){
        $('#canvas_france a').each(function(key, val){
            if( $(this).attr('title') == selectedRegion ){
                $(this).find('path').attr('fill', '#d60030');
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

    // Nouvelles régions
    var regions = ['bretagne', 'paysdelaloire', 'centrevaldeloire', 'iledefrance',
                    'provencealpescotedazur', 'corse', 'normandie', 'poitoucharenteslimousinaquitaine',
                    'midipyreneeslanguedocroussillon', 'picardienordpasdecalais', 'auvergnerhonealpes',
                    'bourgognefranchecomte', 'champagneardennelorrainealsace', 'martinique',
                    'reunion', 'guyane', 'guadeloupe', 'martinique', 'mayotte'];
    
    var regionJSON = JSON.stringify(regions);
    $('#selected-region').val(regionJSON);

    //Supprime la carte
    $('#canvas_france').empty();
    
    //la recrée avec toutes les régions supprimées
    afficheCarteFrance(regionJSON, true);
    
    appliquerRegionsSelectionnees();
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

    appliquerRegionsSelectionnees();
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
