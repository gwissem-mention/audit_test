$(document).ready(function() {
    //Mise en place du validator JS
    if( $('form.toValidate').length > 0 )
        $('form.toValidate').validationEngine();

    $('#hopitalnumerique_rechercheparcours_rechercheparcoursgestion_referencesParentes').select2();
    $('#hopitalnumerique_rechercheparcours_rechercheparcoursgestion_referencesVentilations').select2();
});
