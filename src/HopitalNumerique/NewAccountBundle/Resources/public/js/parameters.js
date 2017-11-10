$(function () {
    Array.prototype.forEach.call(document.querySelectorAll('.toggle'), function (elem) {
        $(elem).toggles({
            on: elem.dataset.toggle,
            text: {on: 'OUI', off: 'NON'}
        })
        .on('toggle', function (evt, active) {
            if (evt.currentTarget.previousElementSibling.id === 'hopitalnumerique_new_account_user_parameters_notificationsSettings_practice_community_user_joined_wanted') {
                evt.currentTarget.parentNode.querySelector('select').value = 'weekly';
            }
            evt.currentTarget.previousElementSibling.checked = active;
            evt.currentTarget.previousElementSibling.classList.toggle('active');

            saveNotificationsSettings();
        })
    ;
    });
    Array.prototype.forEach.call(document.querySelectorAll('#notifications-parameters select'), function (select) {
        if (1 === select.options.length) {
            select.style.display = 'none';
        }
    });
    $('#notifications-parameters .panel-body .content .description select').change(function (evt) {
        evt.target.dataset.value = evt.target.value
    });
    $('form.toValidate').validationEngine();
    $('.slider-input:first').jRange({
        from: 1,
        to: 7,
        step: 1,
        scale: ['L', 'M', 'M', 'J', 'V', 'S', 'D'],
        snap: true,
        format: function (value) {
            $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
            return $days[value - 1];
        },
        ondragend: function () {
            saveNotificationsSettings();
        }
    });
    $('.slider-input:last').jRange({
        from: 0,
        to: 23,
        step: 1,
        scale: ['00h00', '23h00'],
        snap: true,
        format: function (value) {
            return value + 'h00';
        },
        ondragend: function () {
            saveNotificationsSettings();
        }
    });

    $('.notifications-settings-form').find('select, input[type=checkbox]').on('change', function (e) {
        saveNotificationsSettings();
    });

    function saveNotificationsSettings() {
        var $form = $('.notifications-settings-form');

        $.post(
            $form.attr('action'),
            $form.serialize()
        );
    }
});
