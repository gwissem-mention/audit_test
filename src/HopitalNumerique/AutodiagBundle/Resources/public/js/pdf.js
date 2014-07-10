$(document).ready(function() {
    reverse_table(document.getElementById('tableau_resultats'));
    
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
    var nonConcernes = [];
    $(datas).each(function(index, element ){
        if( element.value != 'NC' ){
            categories.push( '<b>' + element.title + '</b>' );
            values.push( element.value );
            optimale.push( element.opti );
        }else
            nonConcernes.push( element.title );
    });

    //Gestion des chapitres non concernes
    if( nonConcernes.length > 0 ){
        var html = '<b>Les chapitres suivants n\'ont pas été diagnostiqués :</b> <ul>';

        $(nonConcernes).each(function(index, element ){
            html += '<li>' + element + '</li>';
        });

        html += '</ul>';
        $('#chaptersNonConcernes').html(html);
    }

    /* Créer le Spider Chart */
    if( categories.length > 0 ) {
        $('#radarChart').highcharts({
            chart : {
                polar     : true,
                type      : 'area',
                width     : 700,
                height    : 750,
                animation : false
            },
            title : {
                text : null
            },
            credits : {
                enabled : false
            },
            pane : {
                size : '80%'
            },
            xAxis : {
                categories        : categories,
                tickmarkPlacement : 'on',
                lineWidth         : 0
            },
            legend:{
                padding : 0
            },
            yAxis : {
                gridLineInterpolation : 'polygon',
                lineWidth             : 0,
                min                   : 0,
                max                   : 110,
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
                pointFormat : '<span style="color:#333333">{series.name}: <b>{point.y:,.0f}</b><br/>'
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
                        format: '<b>{point.y:,.0f}%</b>',
                        softConnector: true,
                        align: 'left'
                    },
                    name  : 'Score',
                    color : '#d9edf7',
                    data  : values,
                    pointPlacement: 'on'
                }, {
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.y:,.0f}%</b>',
                        softConnector: true,
                        align: 'left'
                    },
                    name  : 'Valeur optimale préconisée par l\'ANAP',
                    data  : optimale,
                    color : '#6f3596',
                    type  : 'line',
                    pointPlacement: 'on'
                }
            ]

        });
    }

    //récupère le total et applique la couleur
    noteClass = $('.last-note').attr('class').replace('last-note text-center ','');
    $('.title-total').addClass( noteClass );
});


var copyStyle = function(source, dest)
{
    dest.style.cssText = source.style.cssText;
    dest.className = source.className;
    dest.id = source.id;
}

var reverse_table = function(thetable)
{
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
