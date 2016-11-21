var CompareForm = function(element, domainChooser)
{
    this.element = element;
    this.domainChooser = domainChooser;
    this.init();
};

CompareForm.prototype = {
    init: function ()
    {
        this.bindFormEvents();

        if (this.getDomainChooserElement()) {
            this.bindDomainChooser();
        }
    },

    bindFormEvents: function()
    {
        var instance = this;
        var $form = this.getForm();
        var $reference = $('#compare_reference', this.element);

        $reference.change(function() {
            instance.onReferenceChanged();
        });

        $form.submit(function(e) {
            e.preventDefault();
            instance.onFormSubmit();
        });

        if ($reference.val().length > 0) {
            $reference.trigger('change');
        }
    },

    bindDomainChooser: function()
    {
        var instance = this;
        var $chooser = this.getDomainChooserElement();
        $chooser.on('change', function() {
            instance.updateDomain($(this).find('option:selected'));
        });
    },

    onReferenceChanged: function()
    {
        var $form = this.getForm();
        var $reference = $('#compare_reference', this.element);
        var loader = $form.parent().nodevoLoader().start();
        var data = {};

        data[$reference.attr('name')] = $reference.val();
        // Submit data via AJAX to the form's action path.
        $.ajax({
            url : $form.attr('action'),
            type: $form.attr('method'),
            data : data,
            success: function(html) {
                loader.finished();
                $('#compare_synthesis').replaceWith(
                    $(html).find('#compare_synthesis')
                );
            }
        });
    },

    onFormSubmit: function()
    {
        var instance = this;
        var loader = $('button[type="submit"]', this.element).nodevoLoader().start();
        var $form = this.getForm();

        var xhr = $.ajax({
            url : $form.attr('action'),
            type: $form.attr('method'),
            data : $form.serialize()
        });

        xhr.complete(function(response) {
            loader.finished();
            var redirect = response.getResponseHeader('REDIRECT');
            if (null !== redirect) {
                window.location.href = redirect;
                return;
            } else {
                $form.html($(response.responseText).find('form'));
                instance.bindFormEvents();
            }
        });
    },

    updateDomain: function(element)
    {
        var instance = this;
        var loader = this.element.nodevoLoader().start();

        $.get(element.data('url'), null, function(data) {
                loader.finished();
                instance.getForm().html($('form', data));
                instance.bindFormEvents()
            }
        );
    },

    getForm: function()
    {
        return $('form', this.element);
    },

    getDomainChooserElement: function()
    {
        return this.domainChooser;
    }
};
