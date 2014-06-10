$(document).ready(function() {
    $('#mesrequetes.closed').click(function(){
        //on ouvre
        if ( $(this).hasClass('closed') ){
            wrapper = $('<div />').addClass('lock-screen');
            $('body').append(wrapper);
        //on ferme
        }else{
            $('.lock-screen').remove();
        }
        
        $('#mesrequetes').toggleClass('closed open');
        $('#mesrequetes .content').toggle();
    });

    $("#menu-select").change(function() {
        window.location = $(this).find("option:selected").val();
    });
});