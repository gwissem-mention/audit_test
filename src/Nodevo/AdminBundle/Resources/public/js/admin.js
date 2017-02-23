$(document).ready(function() {
    $.validationEngineLanguage.allRules.numberVirgule = {
        "regex": /^[\-\+]?([0-9]+)?([\,]([0-9]+))?$/,
        "alertText": "* Nombre flottant invalide"
    };
    $('form.toValidate').validationEngine();

    $('.fancybox').fancybox();

    $('.select2').select2();

    $(".fancybox").fancybox({
        afterShow: function() {
            $("form").validationEngine({promptPosition : "topRight:-100"});
        }
    });
});
