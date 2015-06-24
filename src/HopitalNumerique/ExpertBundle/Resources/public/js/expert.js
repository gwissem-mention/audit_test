$(document).ready(function() {
    $( ".mosaicflow__item" ).hover(function() {
            $( this ).find('.description-mosaique').show();
        }, function() {
            $( this ).find('.description-mosaique').hide();
        }
    );
});