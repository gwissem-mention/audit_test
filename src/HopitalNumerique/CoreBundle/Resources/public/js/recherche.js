function rechercheAideEtBesoin()            
{
    var url = $('#fancy-recherche-url').val();

    $.fancybox.open({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '50%',
        'height'    : '360px',
        'scrolling' : 'no',
        'modal'     : true,
        'type'      : 'ajax',
        'href'      : url
    });
}

$(function() {
    $('#test').fancybox({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '80%',
        'scrolling' : 'no',
        'showCloseButton' : true,
        'height' : '360px'
    });
});
//fancybox daffichage de la synthese
enquire.register("screen and (max-width: 991px)", {
    match : function() {
        $(function() {
            $(document).unbind('click.fb-start');
        });
    },
    unmatch : function() {
        $(function() {
            $('#test').fancybox({
                'padding'   : 0,
                'autoSize'  : false,
                'width'     : '80%',
                'scrolling' : 'no',
                'height' : '360px',
                'showCloseButton' : true,
            });
            
        });
    }
});