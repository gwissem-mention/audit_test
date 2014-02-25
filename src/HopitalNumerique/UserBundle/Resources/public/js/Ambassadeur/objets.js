$(document).ready(function() {
    $('.fancy').fancybox({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '40%',
        'height'    : '300px',
        'scrolling' : 'no',
        'modal'     : true
    });
});

//Sauvegarde de la liaison ambassadeur => objets
function addObjet( id )
{
    $.ajax({
        url  : $('#add-objet-url').val(),
        data : {
            ambassadeur : id,
            objets      : $('#objets-ambassadeurs').val()
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            window.location = data.url;
        }
    });
}