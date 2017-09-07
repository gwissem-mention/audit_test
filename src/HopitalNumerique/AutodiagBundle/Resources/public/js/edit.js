
var AutodiagEdit = function(form) {
    this.form = form;
    this.init();
};

AutodiagEdit.prototype = {
    init: function()
    {
        this.initToggles();
    },

    initToggles: function()
    {
        this.form.find('.toggle').each(function(){
            var formGroup = $(this).closest('.form-group');
            var reasonWrap = formGroup.find('.update-reason-container');
            var reasonInput = reasonWrap.find(':input');
            var notifyValue = formGroup.find(':hidden[id$="notify_update"]');

            notifyValue.val('0');
            $(this).toggles({
                on: false,
                text: {
                    on: 'OUI',
                    off: 'NON'
                }
            }).on('toggle', function (e, active) {
                if (active) {
                    reasonWrap.removeClass('hide');
                    reasonInput.focus();
                    notifyValue.val('1');
                } else {
                    reasonWrap.addClass('hide');
                    reasonInput.val('');
                    notifyValue.val('0');
                }
            });
        });
    }
};
