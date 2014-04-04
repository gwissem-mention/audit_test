$(document).ready(function() {
    $('.panel-heading').click(function(){
        $(this).toggleClass('open closed');
        $(this).parent().find('.panel-body').slideToggle();
    })
});
