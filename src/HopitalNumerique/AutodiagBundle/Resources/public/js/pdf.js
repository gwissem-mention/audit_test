$(document).ready(function() {
    //cache les chapitres inutiles
    chaptersToHide = jQuery.parseJSON($('#chaptersToHide').val());
    $.each(chaptersToHide, function(index, value) {
        $('#chapitres .chapitre-' + value).hide();
    });

    //si on a cacher TOUS les chapitres, on affiche le message empty
    allHidden = true;
    $('#chapitres .chapter').each(function(){
        if( $(this).css('display') == 'block' )
            allHidden = false;
    });

    if( allHidden )
        $('#chaptersToHide').parent().show();
    
    /* Manage values */
    var datas      = $.parseJSON( $('#datas-radar').val() );
    var categories = [];
    var values     = [];
    var optimale   = [];
    $(datas).each(function(index, element ){
        categories.push( element.title + ' ( '+ element.taux+'% )' );
        values.push( element.value );
        optimale.push( element.opti );
    })

    /* Créer le Spider Chart */
    $('#radarChart').highcharts({
        chart : {
            polar : true,
            type  : 'area',
            width : 800,
            animation: false
        },
        title : {
            text : null
        },
        credits : {
            enabled : false
        },
        pane : {
            size : '90%'
        },
        xAxis : {
            categories        : categories,
            tickmarkPlacement : 'on',
            lineWidth         : 0
        },
        yAxis : {
            gridLineInterpolation : 'polygon',
            lineWidth             : 0,
            min                   : 0,
            max                   : 100,
            tickInterval          : 20,
            gridLineDashStyle     : 'Dash',
            labels                : {
                enabled : false
            }
        },
        tooltip : {
            shared      : true,
            pointFormat : '<span style="color:{series.color}">{series.name}: <b>{point.y:,.0f}%</b><br/>'
        },
        plotOptions : {
            series : {
                animation : false
            }
        },
        series : [
            {
                dataLabels: {
                    enabled: true,
                    format: '{point.y:,.0f}%',
                    softConnector: true
                },
                name  : 'Mon résultat',
                color : '#ff0000',
                data  : values
            },
            {
                dataLabels: {
                    enabled: true,
                    format: '{point.y:,.0f}%',
                    softConnector: true
                },
                name  : 'Valeur optimale préconisée par l\'ANAP',
                data  : optimale,
                color : '#6f3596',
                type  : 'line'
            }
        ]
    });

    //récupère les note des chapitres et applique la couleur
    $('.total-chapitre').each(function(){
        noteClass = $(this).attr('class').replace('total-chapitre text-center ','');
        $('.chapitre-'+$(this).data('chapitre')).addClass( noteClass );
    });

    //récupère les note des catégories et applique la couleur
    $('.total-categorie').each(function(){
        noteClass = $(this).attr('class').replace('total-categorie text-center ','');
        $('.categorie-'+$(this).data('categorie')).addClass( noteClass );
    });

    //récupère le total et applique la couleur
    noteClass = $('.last-note').attr('class').replace('last-note text-center ','');
    $('.title-total').addClass( noteClass );
});