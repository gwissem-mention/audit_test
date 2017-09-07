var gridster;

$(function () {
    var $grid = $(".gridster ul");
    gridster = $grid.gridster({
        widget_margins: [10, 10],
        widget_base_dimensions: [573, 225],
        max_cols: 2,
        min_cols: 2,
        shift_larger_widgets_down: false,
        serialize_params: function ($w, wgd) {
            return {col: wgd.col, row: wgd.row, id: $w.data('id')}
        },
        draggable: {
            handle: '.draggable',
            stop: function (event, ui) {
                $.ajax({
                    url: $grid.data('reorder-uri'),
                    data: {
                        datas: gridster.serialize()
                    },
                    type: 'POST',
                    dataType: 'json',
                    success: function (data) {
                    }
                });
            }
        }
    }).data('gridster');



    $('.memberActivityRatio').tooltip({
        placement: 'left',
        html: true,
        title: function () {
            return $(this).siblings('.memberActivityRatio-tooltipContent').html();
        }
    })
});
