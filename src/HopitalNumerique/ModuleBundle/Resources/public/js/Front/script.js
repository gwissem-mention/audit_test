$(document).ready(function() {
    $('a.link').fancybox({
        'padding'   : 0,
        'scrolling' : 'auto',
        'width'     : '70%',
        'height'    : 'auto'
    });

    //bind de Validation Engine
    $('form.toValidate').validationEngine();
});
