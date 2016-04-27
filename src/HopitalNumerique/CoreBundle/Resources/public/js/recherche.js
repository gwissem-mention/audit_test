function rechercheAideEtBesoin()            
{
    var url = $('#fancy-recherche-url').val();
    
    $.fancybox.open({
        'padding'   : 0,
        'autoSize'  : false,
/*        'width'     : '70%',
        'maxWidth'  : '1000px',*/
        'autoWidth' : true,
        'scrolling' : 'no',
        'showCloseButton' : true,
        'height'    : '360px',
        'type'      : 'iframe',
        'fitToView' : true,
        'href'      : url
    });

}

$(function() {
    $('#recherche-aidee').fancybox({
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
            $('#recherche-aidee').fancybox({
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

function calcHeightIframe()
{
  //change la hauteur de l'iframe
  document.getElementById('iframe-recherche-tinymce').height = document.getElementById('iframe-recherche-tinymce').contentWindow.document.body.scrollHeight;
}