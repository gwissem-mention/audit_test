$(document).ready(function() {
    //fancybox daffichage de la synthese
    $('a.synthese').fancybox({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '80%',
        'scrolling' : 'no',
        'modal'     : true
    });

    $('#sommaire.closed').click(function(){
        //on ouvre
        if ( $(this).hasClass('closed') ){
            wrapper = $('<div />').addClass('lock-screen');
            $('body').append(wrapper);
        //on ferme
        }else{
            $('.lock-screen').remove();
        }
        
        $('#sommaire').toggleClass('closed open');
        $('#sommaire .content').toggle();
    });
});