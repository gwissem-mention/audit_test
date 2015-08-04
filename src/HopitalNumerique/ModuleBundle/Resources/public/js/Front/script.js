$(function() {
    $('a.link').fancybox({
        'padding'   : 0,
        'scrolling' : 'auto',
        'width'     : '70%',
        'height'    : 'auto'
    });
});

function annulationInscription(url)
{
    apprise(
        'Vous-êtes sur le point d\'annuler votre inscription ', {
            'confirm'   : true, 
            'textOk'    : 'Continuer',
            'textCancel': 'Annuler' 
        }, function(r) {
            if(r) 
            {
                $.ajax({
                    url  : url,
                    type     : 'post',
                    dataType : 'json',
                    success  : function( data ){
                        window.location.reload();
                    }
                });
            }
        }
    );
}
   