$(document).ready(function() {
    $('.selectPublication').fancybox({
        'padding'   : 0,
        'scrolling' : 'no',
        'type'      : 'ajax',
        'href'      : $('#objets-liste-url').val()
    });
});

/**
 * Enregistre le lien vers la publication
 */
function savePublication()
{
    $.ajax({
        url  : $('#objets-details-menu-url').val(),
        data : {
            publication : $('#publication').val()
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            $('#nodevo_menu_item_route option[value="'+data.url+'"]').prop('selected', true);

            //Common part for Objet and Contenu
            html = '<div class="form-group">';
                html += '<label for="nodevo_menu_item_routeParameters_routeParameters_id" class="col-md-3 control-label">';
                    html += 'id';
                html += '</label>';
                html += '<div class="col-md-9">';
                    html += '<input type="text" id="nodevo_menu_item_routeParameters_routeParameters_id" name="nodevo_menu_item[routeParameters][routeParameters_id]" class="form-control" value="'+data.id+'" >';
                html += '</div>';
            html += '</div>';
            html += '<div class="form-group">';
                html += '<label for="nodevo_menu_item_routeParameters_routeParameters_alias" class="col-md-3 control-label">';
                    html += 'alias';
                html += '</label>';
                html += '<div class="col-md-9">';
                    html += '<input type="text" id="nodevo_menu_item_routeParameters_routeParameters_alias" name="nodevo_menu_item[routeParameters][routeParameters_alias]" class="form-control" value="'+data.alias+'">';
                html += '</div>';
            html += '</div>';

            //Only For Contenu (infra doc)
            if(data.url == 'hopital_numerique_recherche_publication_contenu'){
                html += '<div class="form-group">';
                    html += '<label for="nodevo_menu_item_routeParameters_routeParameters_idc" class="col-md-3 control-label">';
                        html += 'idc';
                    html += '</label>';
                    html += '<div class="col-md-9">';
                        html += '<input type="text" id="nodevo_menu_item_routeParameters_routeParameters_idc" name="nodevo_menu_item[routeParameters][routeParameters_idc]" class="form-control" value="'+data.idc+'" >';
                    html += '</div>';
                html += '</div>';
                html += '<div class="form-group">';
                    html += '<label for="nodevo_menu_item_routeParameters_routeParameters_aliasc" class="col-md-3 control-label">';
                        html += 'aliasc';
                    html += '</label>';
                    html += '<div class="col-md-9">';
                        html += '<input type="text" id="nodevo_menu_item_routeParameters_routeParameters_aliasc" name="nodevo_menu_item[routeParameters][routeParameters_aliasc]" class="form-control" value="'+data.aliasc+'">';
                    html += '</div>';
                html += '</div>';
            }

            $('#nodevo_menu_item_routeParameters').html( html );

            $.fancybox.close(true);
        }
    });
}