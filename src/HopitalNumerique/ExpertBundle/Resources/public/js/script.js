$(document).ready(function() {

    //Date début
    $( "#hopitalnumerique_expert_activiteexpert_dateDebut" ).datepicker({
        defaultDate: "now",
        changeMonth: true,
        numberOfMonths: 1,
        dateFormat: "yy-mm-dd",
        onClose: function( selectedDate ) {
            $( "#hopitalnumerique_expert_activiteexpert_dateFin" ).datepicker( "option", "minDate", selectedDate );
        }
    });
    $( "#hopitalnumerique_expert_activiteexpert_dateDebut" ).datepicker( "option", "showAnim", "fadeIn" );

    //Date de fin
    $( "#hopitalnumerique_expert_activiteexpert_dateFin" ).datepicker({
        defaultDate: "+1d",
        changeMonth: true,
        numberOfMonths: 1,
        dateFormat: "yy-mm-dd",
        onClose: function( selectedDate ) {
            $( "#hopitalnumerique_expert_activiteexpert_dateDebut" ).datepicker( "option", "maxDate", selectedDate );
        }
    });
    $( "#hopitalnumerique_expert_activiteexpert_dateFin" ).datepicker( "option", "showAnim", "fadeIn" );

    $('select.select2').select2();

    //bind de Validation Engine
    if( $('form.toValidate').length > 0 )
    {
        $('form.toValidate').validationEngine();
    }

    //Date fictive
    $( "#date_fictive" ).datepicker({
        defaultDate: "now",
        changeMonth: true,
        numberOfMonths: 1,
        dateFormat: "yy-mm-dd"
    });

});

//Selectionne un chapitre et charge l'ensemble des questions liés
function addDateFictive( url )
{
    if ( $('#form_date_fictive form').validationEngine('validate') ) {
        $.ajax({
            url     : url,
            data    :  $('#form_date_fictive form').serialize(),
            type    : 'POST',
            success : function( data ){
                // Refresh de la liste
                $('#dates-fictives').html( data );
            }
        });
    }
}