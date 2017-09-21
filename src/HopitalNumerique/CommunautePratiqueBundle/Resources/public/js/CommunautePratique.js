/**
 * Classe générale concernant la communauté de pratique.
 */
var CommunautePratique = function() {};


$(document).ready(function() {
    CommunautePratique.init();
});

$(window).load(function() {
    CommunautePratique.sizeBlock();
});


/**
 * Initialisation.
 */
CommunautePratique.init = function()
{
    $('select.select2').select2();
    $('form.toValidate').validationEngine();
    $('.fancybox').fancybox({
        autoSize: false,
        autoHeight: true,
        width : 600
    });

    
    $('a.synthese').fancybox({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '80%',
        'scrolling' : 'no'
    });

    CommunautePratique.selectedDomainSelectorEvent();
};

CommunautePratique.selectedDomainSelectorEvent = function () {
    $('#cdpSelectedDomain').on('change', function (e) {

        $(this).parents('.domain_selector_wrapper').nodevoLoader().start();

        $.post($(this).data('uri'), {'selected_domain': $(this).val()}, function (response, code) {
            location.reload();
        });
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
 * Can take a callback in parameter to modify the success behavior
 */
CommunautePratique.desinscrit = function(callback)
{
    callback = undefined === callback ? function (data) {
        window.location = data.responseJSON.url;
    } : callback;

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
                    complete: callback
                });
            }
        }
    );
};

CommunautePratique.sizeBlock = function() {
    if($(window).width() > 1000) {
        $('#panel-communaute-de-pratiques-actualites').each(function () {
            var h1 = $('#panel-communaute-de-pratiques-forums');
            var h2 = $(this).height();
            if (h1.height() > h2) {
                $(this).height(h1.height());
            }
            else {
                h1.height(h2);
            }
        });
    }
}
