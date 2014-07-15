$(document).ready(function() {
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
        $(this).find('.carousel-inner .item:first').addClass('active');
        $(this).find('.carousel-indicators li.pos-1').addClass('active');
    });

    $('#autresResultats h2').click(function(){
        $(this).toggleClass('open closed');
        $('#autresResultats .row').slideToggle();
    });

    $('#productions h2').click(function(){
        $(this).toggleClass('open closed');
        $('#productions .row').slideToggle();
    });

    //Style WYSIWYG custom : titre pliable
    $('h2 .titre_depliable').click(function(){
        $(this).parent().nextAll().each(function(){
            if ( !$(this).find('span').hasClass('titre_depliable') )
                $(this).slideToggle();
            else
                return false;
        });
    });

    //Default collapse all
    $('h2 .titre_depliable').each(function(){
        $(this).parent().nextAll().each(function(){
            if ( !$(this).find('span').hasClass('titre_depliable') )
                $(this).hide();
        });
    });

    $('a.synthese').fancybox({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '80%',
        'scrolling' : 'no'
    });
});

//fancybox daffichage de la synthese
enquire.register("screen and (max-width: 991px)", {
    match : function() {
        $(function() {
            $(document).unbind('click.fb-start');
            $('a.synthese').attr('target','_blank');
        });
    },
    unmatch : function() {
        $(function() {
            $('a.synthese').fancybox({
                'padding'   : 0,
                'autoSize'  : false,
                'width'     : '80%',
                'scrolling' : 'no'
            });
            $('a.synthese').attr('target','');
        });
    }
});