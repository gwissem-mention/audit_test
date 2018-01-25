$(function() {
    var savePositions = function () {
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
    };
    gridster = $(".gridster ul").gridster({
        widget_margins         : [5, 10],
        widget_base_dimensions : [330, 155],
        min_cols               : 3,
        max_cols               : 3,
        serialize_params       : function($w, wgd) {
            return {
                col: wgd.col,
                row: wgd.row,
                id: $w.data('id')
            };
        },
        draggable              : {
            handle: '.drag-handler',
            stop : function(event, ui){ 
                savePositions();
            }
        }
    }).data('gridster');

    $('#page-content').attr('style', '');





    /**
     * Hide block
     *
     * @param element
     * @param id
     */
    var hideBlock = function (element, id) {
        blocks[id].visible = false;
        element.data('visible', false);
        gridster.remove_widget(element, savePositions);
        addBlockToPopulateSelect(blocks[id]);
    };

    var showBlock = function (block, persist) {
        gridster.add_widget(block.html, 1, 1, block.col, block.row);
        if (persist) {
            savePositions();
        }
        bindBlock(block);
        removeBlockFromPopulateSelect(block);
    };

    /**
     * Add option to hidden blocks select
     *
     * @param block
     */
    var addBlockToPopulateSelect = function (block) {
        var option = $('<option />')
            .attr('data-id', block.id)
            .attr('data-row', block.row)
            .attr('data-col', block.col)
            .html(block.title)
        ;
        $('#populate-block select').append(option);
        $('#populate-block').show();
    };

    var removeBlockFromPopulateSelect = function (block) {
        var option = $('#populate-block option').filter('[data-id="' + block.id + '"]');
        option.remove();

        var select = $('#populate-block select');
        if (select.find('option').length === 0) {
            $('#populate-block').hide();
        }
    }

    /**
     * Bind block events
     * @param block
     */
    var bindBlock = function (block) {
        var block = $('[data-id="' + block.id + '"]', '.gridster');
        $('.hide-block', block).on('click', function () {
            hideBlock($(this).closest('li'), $(this).closest('li').data('id'));
        });
    };

    var blocks = {};

    /**
     * Initialise blocks
     */
    $('.gridster-data > ul > li').each(function () {
        blocks[$(this).data('id')] = {
            id: $(this).data('id'),
            visible: $(this).data('visible'),
            row: $(this).data('row'),
            col: $(this).data('col'),
            html: $(this).get(0).outerHTML,
            title: $(this).find('.title').html()
        };
    });

    /**
     * Initialise render - show visible blocks - populate select with hidden blocks
     */
    $.each(blocks, function (k, block) {
        if (block.visible) {
            showBlock(block, false);
        } else {
            addBlockToPopulateSelect(block);
        }
    });

    $('#populate-block button').on('click', function () {
        var option = $(this).parent().find('select option:selected');
        var block = blocks[option.data('id')];
        showBlock(block, true);
    });
});
