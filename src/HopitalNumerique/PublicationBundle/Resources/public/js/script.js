$(document).ready(function() {
    //fancybox daffichage de la synthese
    $('a.synthese').fancybox({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '80%',
        'scrolling' : 'no'
    });

    /* Gestion de l'ouverture/fermeture du sommaire et de la liste des ambassadeurs */
    $('#sommaire.closed, #ambassadeurs.closed').click(function(){
        //on ouvre
        if ( $(this).hasClass('closed') ){
            wrapper = $('<div />').addClass('lock-screen');
            $('body').append(wrapper);
        //on ferme
        }else{
            $('.lock-screen').remove();
        }
        
        $(this).toggleClass('closed open');
        $(this).find('.content').toggle();
    });

    // Met en place le carousel
    $('.carousel').carousel({
        interval : 50000000
    });

    // Met en place la pagination
    $('.carousel').each(function(){
        if( $(this).find('.carousel-inner .item.active').length == 0 ){
            $(this).find('.carousel-inner .item:first').addClass('active');
            pos = 1;
        }else
            pos = $(this).find('.carousel-inner .item.active').data('pos');

        if( pos != undefined )
            $(this).find('.carousel-indicators li.pos-'+pos).addClass('active');
    });

    $('#autresResultats h2').click(function(){
        $(this).toggleClass('open closed');
        $('#autresResultats .row').slideToggle();
    });
});