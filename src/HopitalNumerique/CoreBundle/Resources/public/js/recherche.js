function rechercheAideEtBesoin()            
{
    var url = $('#fancy-recherche-url').val();

    $.fancybox.open({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '50%',
        'height'    : 'auto',
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
        'height' : 'auto'
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
                'height' : 'auto',
                'showCloseButton' : true,
            });
            
        });
    }
});