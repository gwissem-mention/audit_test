$(document).ready(function() {
    $('#resultats h2').click(function(){
        $('#' + $(this).data('toggle') ).slideToggle();
        $(this).toggleClass('open closed');
    })
});