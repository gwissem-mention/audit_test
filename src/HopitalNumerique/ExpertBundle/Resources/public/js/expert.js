$(document).ready(function() {
    $( ".mosaicflow__item" ).hover(function() {
            $( this ).find('.description-mosaique').show();
        }, function() {
            $( this ).find('.description-mosaique').hide();
        }
    );

    $('.fancy').fancybox({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '70%',
        'scrolling' : 'yes',
        'showCloseButton' : true,
        'height' : 'auto'
    });
});