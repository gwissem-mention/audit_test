$(document).ready(function() {

    //bind de Validation Engine
    if( $('form.toValidate').length > 0 )
    {
        $('form.toValidate').validationEngine();
    }

    //Date
    $( "#hopitalnumerique_expert_evenementexpert_date" ).datepicker({
        defaultDate: "now",
        changeMonth: true,
        numberOfMonths: 1,
        dateFormat: "yy-mm-dd"
    });

});