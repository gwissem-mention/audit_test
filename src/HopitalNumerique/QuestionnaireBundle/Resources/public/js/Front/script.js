$(document).ready(function() {
    //Mise en place du validator JS
    if( $('form.toValidate').length > 0 )
        $('form.toValidate').validationEngine();

    //Ajout du background en dessous du titre sur la vue générée par le questionnaire
    $( "<div class=\"background-rose\"></div>" ).insertAfter( "#form_questionnaire_front form #nodevo_questionnaire_questionnaire" );
});
