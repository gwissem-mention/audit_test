$(document).ready(function() {

    if( $('.publicationsTypes').length > 0 ){
        $('.publicationsTypes').nestable({'maxDepth':1,'group':0}).on('change', function() {

            $(this).find('.item').each(function (k,e) {
                $(e).find('.item-order').val(k+1);
            });
        });
    }

    //Mise en place du validator JS
    if( $('form.toValidate').length > 0 )
        $('form.toValidate').validationEngine();

    $('#hopitalnumerique_rechercheparcours_rechercheparcoursgestion_referencesParentes').select2();
    $('#hopitalnumerique_rechercheparcours_rechercheparcoursgestion_referencesVentilations').select2();
});
