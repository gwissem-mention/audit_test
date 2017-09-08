$(document).ready(function () {
    initSelect2($('.select2'));

    new AjaxList($('.ajax-list-select2'));
    new CountyList($('#nodevo_user_registration_region'), $('#nodevo_user_registration_county'));

    $('form.toValidate').validationEngine();
});

function initSelect2($select) {
    $select.select2({ width: '100%' });
}
