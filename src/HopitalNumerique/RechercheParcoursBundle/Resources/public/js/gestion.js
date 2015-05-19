$(document).ready(function() {
    //Mise en place du validator JS
    if( $('form.toValidate').length > 0 )
        $('form.toValidate').validationEngine();
});
