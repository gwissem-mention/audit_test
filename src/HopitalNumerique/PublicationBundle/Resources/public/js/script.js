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

    $('#commentaires h2').click(function(){
        $(this).toggleClass('open closed');
        $('#commentaires .bloc-commentaire').slideToggle();
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

    //bind de Validation Engine
    if( $('form.toValidate').length > 0 )
        $('form.toValidate').validationEngine();

    //tooltip sur les mot trouvés du glossaire
    $(".glosstool").tooltip({
        placement : 'top'
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

function deleteWithConfirm(path)
{
    apprise('Attention, cette opération est irréversible, êtes-vous sur de vouloir continuer ?', {'verify':true,'textYes':'Oui','textNo':'Non'}, function(r) {
        if(r) { 
            $.ajax({
                url      : path,
                type     : 'POST',
                dataType : 'json',
                success  : function( data ){
                   location.reload();
                }
            });
        }
    });
}

function ajoutCommentaire(path)
{
    var loader = $('#form-ajout').nodevoLoader();

    if ( $('#form-ajout form').validationEngine('validate') ) {
        loader.start();
        
        $.ajax({
            url     : path,
            data    :  $('#form-ajout form').serialize(),
            type    : 'POST',
            success : function( data ){
                //Ajout de la réponse
                $('#nouveau-commentaire').append( data );
                
                loader.finished();
            }
        });
    }
}
