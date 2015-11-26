/**
 * Classe générale concernant la communauté de pratique.
 */
var CommunautePratique = function() {};


$(document).ready(function() {
    CommunautePratique.init();
});


/**
 * Initialisation.
 */
CommunautePratique.init = function()
{
    $('form.toValidate').validationEngine();
    $('.fancybox').fancybox({
        autoSize: false,
        autoHeight: true,
        width : 600
    });
};


/**
 * Inscrit l'utilisateur à la communauté de pratique.
 */
CommunautePratique.inscrit = function()
{
    $.ajax({
        url: '/communaute-de-pratiques/inscription',
        type: 'POST',
        dataType: 'json',
        complete: function( data )
        {
            window.location = data.responseJSON.url;
        }
    });
};

/**
 * Désinscrit l'utilisateur à la communauté de pratique.
 */
CommunautePratique.desinscrit = function()
{
     apprise(
        'Souhaitez-vous quitter la communauté ? Attention tous vos documents et messages seront supprimés.',
        {
            'confirm'   : true,
            'textOk'    : 'Oui',
            'textCancel': 'Non'
        },
        function(r)
        {
            if (r)
            {
                $.ajax({
                    url: '/communaute-de-pratiques/desinscription',
                    type: 'POST',
                    dataType: 'json',
                    complete: function( data )
                    {
                        window.location = data.responseJSON.url;
                    }
                });
            }
        }
    );
};
