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
    $('.select2').select2();
    $('form.toValidate').validationEngine();
    $('.fancybox').fancybox({
        autoSize: false,
        autoHeight: true,
        width : 600
    });
    $(".infobulle").mouseenter(function(e){
        e.preventDefault();
        $(this).children(".groupeContent").fadeIn("slow");
    });
    $(".infobulle").mouseleave(function(e){
        e.preventDefault();
        $(this).children(".groupeContent").fadeOut("fast");
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
