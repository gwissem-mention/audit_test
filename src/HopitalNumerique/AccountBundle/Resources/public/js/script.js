$(function() {
    var gridster = $(".gridster ul").gridster({
        widget_margins         : [5, 10],
        widget_base_dimensions : [410, 200],
        max_cols               : 2,
        min_cols               : 2,
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


// enquire.register("screen and (max-width: 991px)", {
//     match : function() {
//         $(function() {
//             $(document).unbind('click.fb-start');
//             $('a.link').attr('target','_blank');
//         });
//     },
//     unmatch : function() {
//         $(function() {
//             $('a.link').fancybox({
//                 'padding'   : 0,
//                 'scrolling' : 'auto',
//                 'width'     : '70%',
//                 'height'    : 'auto'
//             });
//             $('a.link').attr('target','');
//         });
//     }
// });
