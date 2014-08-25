function rechercheAideEtBesoin()            
{
    var url = $('#fancy-recherche-url').val();

    $.fancybox.open({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '50%',
        'height'    : '400px',
        'scrolling' : 'no',
        'modal'     : true,
        'type'      : 'ajax',
        'href'      : url
    });
}