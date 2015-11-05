$(document).ready(function() {
    reverse_table(document.getElementById('tableau_resultats'));
    
    //cache les chapitres inutiles
    if($('#chaptersToHide').length != 0)
    {
        chaptersToHide = jQuery.parseJSON($('#chaptersToHide').val());
        $.each(chaptersToHide, function(index, value) {
            $('#chapitres .chapitre-' + value).hide();
        });
    }

    //si on a cacher TOUS les chapitres, on affiche le message empty
    allHidden = true;
    $('#chapitres .chapter').each(function(){
        if( $(this).css('display') == 'block' )
            allHidden = false;
    });

    if( allHidden )
        $('#chaptersToHide').parent().show();

    $("#reponses").css("display", "block");


    /* Manage values */
    var datas        = $.parseJSON( $('#datas-radar').val() );
    var categories   = [];
    var values       = [];
    var taux         = [];
    var optimale     = [];
    var nonConcernes = [];
    var min          = [];
    var max          = [];
    var moyennes     = [];
    var deciles2     = [];
    var deciles8     = [];
    var centPourCent = [];

    $(datas).each(function(index, element ){
        if( element.value != 'NC' || element.taux != 0 )
        {
            title = '<b>' + element.title + '</b><span style="display: none;">' + (element.centPourcentReponseObligatoire ? '' : ' (Taux de remplissage: ' + element.taux + '%)') + '</span>';
            categories.push( title );
            values.push( element.value );
            optimale.push( element.opti );
            min.push( element.min );
            max.push( element.max );
            
            if (element.moyennePourcentage != undefined)
                moyennes.push(element.moyennePourcentage);
            if (element.decile2Pourcentage != undefined)
                deciles2.push(element.decile2Pourcentage);
            if (element.decile8Pourcentage != undefined)
            {
                deciles8.push(element.decile8Pourcentage);
                centPourCent.push(100);
            }

            taux[ title ] = element.taux;
        }
        else
        {
            nonConcernes.push( element.title );
        }
    });

    //Gestion des chapitres non concernes
    if ( nonConcernes.length > 0 )
    {
        $('#chaptersNonConcernes').html('<b>Les éléments suivants n\'ont pas été diagnostiqués :</b> ' + nonConcernes.join(', ') + '.');
    }

    var seriesRadar = [];

    if(!jQuery.isEmptyObject(deciles8) && deciles8[0] != null )
    {
        seriesRadar.push({
            dataLabels: {
                enabled: true,
                format: ' ',
                softConnector: true,
                align: 'left'
            },
            name  : 'Huitième décile',
            marker: { symbol:'circle' },
            data  : deciles8,
            color : radarChartBenchmarkCouleurDecile8,
            lineWidth: 2,
            lineColor: radarChartBenchmarkCouleurDecile8,
            type  : 'line',
            pointPlacement: 'on'
        });
    }
    if(!jQuery.isEmptyObject(deciles2) && deciles2[0] != null )
    {
        seriesRadar.push({
            dataLabels: {
                enabled: true,
                format: ' ',
                softConnector: true,
                align: 'left'
            },
            name  : 'Deuxième décile',
            marker: { symbol:'circle' },
            data  : deciles2,
            color : radarChartBenchmarkCouleurDecile2,
            lineWidth: 2,
            lineColor: radarChartBenchmarkCouleurDecile2,
            type  : 'line',
            pointPlacement: 'on'
        });
    }
    if(!jQuery.isEmptyObject(moyennes) && moyennes[0] != null )
    {
        seriesRadar.push({
            dataLabels: {
                enabled: true,
                format: ' ',
                softConnector: true,
                align: 'left'
            },
            name  : 'Moyenne du benchmark',
            marker: { symbol:'circle' },
            data  : moyennes,
            color : '#777777',
            lineWidth:1,
            lineColor: '#777777',
            type  : 'line',
            pointPlacement: 'on'
        });
    }
    if(!jQuery.isEmptyObject(max) && max[0] != null )
    {
        seriesRadar.push({
            dataLabels: {
                enabled: true,
                //format: '<b>{point.y:,.0f}%</b>',
                format: ' ',
                softConnector: true,
                align: 'left'
            },
            name  : 'Valeur maximale de la synthèse',
            marker: { symbol:'circle' },
            data  : max,
            color : '#00aa00',
            type  : 'line',
            lineWidth: 2,
            lineColor: '#00aa00',
            pointPlacement: 'on'
        });
    }
    
    if(!jQuery.isEmptyObject(min) && min[0] != null )
    {
        seriesRadar.push({
            dataLabels: {
                enabled: true,
                //format: '<b>{point.y:,.0f}%</b>',
                format: ' ',
                softConnector: true,
                align: 'left'
            },
            name  : 'Valeur minimale de la synthèse',
            marker: { symbol:'circle' },
            data  : min,
            color : '#aa0000',
            type  : 'line',
            lineWidth: 2,
            lineColor: '#aa0000',
            pointPlacement: 'on'
        });
    }

    var opti = false;
    $.each(optimale, function(key, elem){
        if( elem != null ){
            opti = true;
        }
    });
    if( opti ){
        seriesRadar.push({
            dataLabels: {
                enabled: true,
                format: '<b>{point.y:,.0f}%</b>',
                softConnector: true,
                align: 'left'
            },
            name  : 'Valeur optimale préconisée par l\'ANAP',
            data  : optimale,
            color : '#d60030',
            type  : 'line',
            lineWidth: 2,
            lineColor: '#d60030',
            pointPlacement: 'on'
        });
    }
    
    seriesRadar.push({
        dataLabels: {
            enabled: true,
            formatter: function() {
                var tau = taux[this.point.category];
                return ( tau == 0 && this.point.series.name == (estSynthese ? 'Valeur moyenne de la synthèse' : 'Votre résultat') ) ? '' : '<b>' + number_format(this.point.y, 0) + '%</b>'
            },
            softConnector: true,
            align: 'left'
        },
        name  : (estSynthese ? 'Valeur moyenne de la synthèse' : 'Votre résultat'),
        color : '#6f3596',
        marker: { symbol:'circle' },
        type  : 'line',
        lineWidth: 2,
        lineColor: '#6f3596',
        data  : values,
        pointPlacement: 'on'
    });

    /* Créer le Spider Chart */
    if( categories.length > 0 ) {
        $('#radarChart').highcharts({
            chart : {
                polar  : true,
                type   : 'area'
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
            legend:{
                padding : 20,
                reversed: true,
                itemStyle: { "color": "#333333", "cursor": "pointer", "fontSize": "10px", "fontWeight": "bold" }
            },
            yAxis : {
                gridLineInterpolation : 'polygon',
                lineWidth             : 0,
                min                   : 0,
                max                   : 100,
                tickInterval          : 10,
                gridLineDashStyle     : 'Dash',
                labels                : {
                    enabled : true,
                    formatter: function() {
                        return this.value +'%';
                    }
                }
            },
            tooltip : {
                shared      : true,
                formatter: function() {
                    var s   = '';
                    var tau = taux[this.x]

                    $.each(this.points, function(i, point) {
                        if ('hideLabel' != point.series.name)
                        {
                            val = ( tau == 0 && point.series.name == (estSynthese ? 'Valeur moyenne de la synthèse' : 'Votre résultat') ) ? 'NC' : number_format(point.y, 0) + '%';
                            s = '<br/><span style="color:#333333; font-size:10px">'+ point.series.name + ': '+ val + '</span>' + s;
                        }
                    });
                    
                    return this.x + s;
                }
            },
            series : seriesRadar
        });
    }

    //récupère le total et applique la couleur
    if( $('.last-note').length > 0 ){
        noteClass = $('.last-note').attr('class').replace('last-note text-center ','');
        $('.title-total').addClass( noteClass );
    }
});


var copyStyle = function(source, dest)
{
    dest.style.cssText = source.style.cssText;
    dest.className = source.className;
    dest.id = source.id;
}

var reverse_table = function(thetable)
{
    if (thetable == undefined)
        return;
    
    var rtable = thetable.cloneNode(false);
    var rtable_body = rtable.appendChild(document.createElement("tbody"));
    
    // On compte les colonnes
    var tableNumCols = 0;
    for(var tdi = 0; tdi < thetable.rows[0].cells.length; tdi++)
    {
        tableNumCols += thetable.rows[0].cells[tdi].colSpan;
    }
    
    // On compte les lignes
    var tableNumRows = 0;
    for(var tri = 0; tri < thetable.rows.length; tri++)
    {
        tableNumRows += thetable.rows[tri].cells[0].rowSpan;
    }
    
    // on crée les tr dont on a besoin
    var rtable_trs = Array();
    var rowSpanDuration = Array();
    var rowSpanValue = Array();
    var cols = thetable.getElementsByTagName("col");
    for(var tri = 0; tri < tableNumCols; tri++)
    {
        var current_tr = document.createElement("tr");
        if(tri < cols.length)
        {
            copyStyle(cols[tri], current_tr);
        }
        rtable_trs[tri] = current_tr;
        rtable_body.appendChild(current_tr);
        rowSpanDuration[tri] = 1;
        rowSpanValue[tri] = 1;
    }
    
    // On va retourner le tableau maintenant
    var celli = 0;
    for(var tri = 0; tri < thetable.rows.length; tri++)
    {
        var col = document.createElement("col");

        copyStyle(thetable.rows[tri], col);
        rtable.appendChild(col);
        
        for(var tdi = 0; tdi < thetable.rows[tri].cells.length; tdi++)
        {
            var cell = thetable.rows[tri].cells[tdi].cloneNode(true);
            
            // Calcul de la position d'insertion
            var rowOfInsertion = celli % tableNumCols;
            
            // Gestion des row span
            var rsdi = rowOfInsertion;
            
            while(rowSpanDuration[rsdi] > 1)
            {
                celli += rowSpanValue[rsdi];
                rowOfInsertion = celli % tableNumCols; // maj de la position d'insertion
                rowSpanDuration[rsdi]--;
                rsdi = rowOfInsertion;
            }
            rowSpanDuration[rsdi] += cell.rowSpan - 1;
            rowSpanValue[rsdi] = cell.colSpan;
            
            // échanger rowSpan and colSpan
            var colSpan = cell.colSpan;
            cell.colSpan = cell.rowSpan;
            cell.rowSpan = colSpan;
            
            // Insertion de la cellule
            rtable_trs[rowOfInsertion].appendChild(cell);
            
            // Gestion des col span
            celli += colSpan; // colSpan vaut 1 pour une cellule classique
        }
    }
    
    thetable.parentNode.replaceChild(rtable, thetable);
}

/**
 * [number_format description]
 *
 * @param  {[type]} number        [description]
 * @param  {[type]} decimals      [description]
 * @param  {[type]} dec_point     [description]
 * @param  {[type]} thousands_sep [description]
 *
 * @return {[type]}
 */
function number_format(number, decimals, dec_point, thousands_sep)
{
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n    = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep  = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec  = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s    = '',
        toFixedFix = function(n, prec) {
          var k = Math.pow(10, prec);
          return '' + (Math.round(n * k) / k)
            .toFixed(prec);
        };

    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3)
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    
    return s.join(dec);
}
