var AutodiagEntry = function(element) {
    this.element = element;
    this.init();

    this.options = {
        saveDelay: 500
    };
};

AutodiagEntry.prototype = {
    init: function()
    {
        this.bindFormSubmit();
    },

    bindFormSubmit: function()
    {
        var self = this;
        $('input, select, textarea', this.element).on('change', function() {
            console.log($(this).data('changedTimer'));
            if ($(this).data('changedTimer') !== undefined) {
                clearTimeout($(this).data('changedTimer'));
            }

            var form = $(this).closest('form');
            $(this).data('changedTimer', setTimeout(function() {
                self.submitForm(form);
            }, self.options.saveDelay));
        });
    },

    submitForm: function(form)
    {
        $.post(form.get(0).action, form.serialize(), function() {
            console.log('saved');
        });
    }
};
