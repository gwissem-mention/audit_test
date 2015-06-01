$(document).ready(function() {
    $('.create').on('click', function(){
        var resultats    = [];
        var typeOutil    = null;
        var sameAutodiag = true;

        $('.autodiagTable .checkbox').each(function(){
            if( $(this).prop('checked') ){
                resultats.push( $(this).val() );

                if( typeOutil == null )
                    typeOutil = $(this).data('outil');

                if( typeOutil != $(this).data('outil') )
                    sameAutodiag = false;
            }
        });

        //Do stuff for min 2 results and only if from same autodiag
        if( resultats.length >= 2 && sameAutodiag ) {
            apprise('Merci de saisir un nom pour cette synthèse', {'input':true,'textOk':'Valider','textCancel':'Annuler'}, function(r) {
                if(r) { 
                    $.ajax({
                        url  : $('#generate-synthese-url').val(),
                        data : {
                            outil     : typeOutil,
                            resultats : resultats,
                            nom       : r
                        },
                        type     : 'POST',
                        dataType : 'json',
                        success  : function( data ){
                            window.location = data.url;
                        }
                    });
                }else
                    apprise('Merci de saisir un nom valide.');
            });            
        }else if( resultats.length < 2 )
            apprise('Merci de sélectionner au moins 2 résultats.');
        else
            apprise('Les résultats sélectionnés ne proviennent pas du même résultat d\'autodiagnostic.');
    });

    idDomaineCurrent = $('#domaine-current-id').val();

    $('#tableau-de-bord .content .panel .onglets .onglet').each(function(){
        var domaineSelected = $(this).data('id');

        $(this).on('click', function()
        {
            $('#tableau-de-bord .content .panel .autodiag-domaine').each(function(){
                if($(this).data('id') != domaineSelected)
                {
                    $("#onglet-" + $(this).data('id')).removeClass('active');
                    $(this).hide();
                }
                else
                {
                    $("#onglet-" + $(this).data('id')).removeClass('active').addClass('active');
                    $(this).show();
                }
            });
        });
    });
});

/**
 * Reprise de la fonction ADMIN:deletewithConfirm
 */
function deleteWithConfirm( path )
{
    apprise('Attention, cette opération est irréversible, êtes-vous sur de vouloir continuer ?', {'verify':true,'textYes':'Oui','textNo':'Non'}, function(r) {
        if(r) { 
            $.ajax({
                url      : path,
                type     : 'POST',
                dataType : 'json',
                success : function( data ){
                    window.location = data.url;
                }
            });
        }
    });
}

$(function() {
    $('.share-fancy').fancybox({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '50%',
        'scrolling' : 'no',
        'showCloseButton' : true,
        'height' : '290px'
    });
});