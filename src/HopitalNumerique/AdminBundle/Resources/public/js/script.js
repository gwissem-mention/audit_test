$(function() {
    gridster = $(".gridster ul").gridster({
        widget_margins         : [5, 10],
        widget_base_dimensions : [330, 200],
        max_cols               : 3,
        min_cols               : 3,
        serialize_params       : function($w, wgd) { return { col: wgd.col, row: wgd.row, id: $w.data('id') } },
        draggable              : {
            stop : function(event, ui){ 
                $.ajax({
                    url  : $('#account-reorder-url').val(),
                    data : {
                        datas : gridster.serialize()
                    },
                    type     : 'POST',
                    dataType : 'json',
                    success  : function( data ){
                       //reorder done with success
                    }
                });
            }
        }
    }).data('gridster');
});