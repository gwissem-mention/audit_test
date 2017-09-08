$(document).ready(function() {
    //Mise en place du validator JS
    if( $('form.toValidate').length > 0 )
        $('form.toValidate').validationEngine();

    //Ajout du background en dessous du titre sur la vue générée par le questionnaire
    $( "<div class=\"background-rose\"></div>" ).insertAfter( "#form_questionnaire_front form #nodevo_questionnaire_questionnaire" );
});

(function() {
    $(function() {
        var $select = $('.ajax-list-select2');
        $select.select2({
            ajax: {
                url: $select.data('url'),
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term,
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            },
            minimumInputLength: 3,
            language: {
                inputTooShort: function (args) {
                    var remainingChars = args.minimum - args.input.length;

                    var message = 'Saisir ' + remainingChars + ' caractère';

                    if (remainingChars !== 1) {
                        message += 's';
                    }

                    message += ' du nom de votre structure, de la ville ou de son FINESS';

                    return message;
                }
            }
        });
    });
})();
