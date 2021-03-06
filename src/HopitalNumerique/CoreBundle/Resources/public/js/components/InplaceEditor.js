/**
 * Let users edit values in the same place as they are displayed.
 */
var InplaceEditor = (function() {

    InplaceEditor = function(field, url, options) {
        this.field = field;
        this.url = url;
        this.text = field.text();
        this.fieldLink  = field.data('field-link');
        this.fieldLinkTarget  = field.data('field-link-target');

        this.options = $.extend({
            title: 'Edit',
            text: {
                cssClass: "inplace-edit-text"
            },
            editButton: {
                cssClass: "inplace-edit-btn",
                logo: {
                    cssClass: "fa fa-edit"
                }
            },
            input: {
                cssClass: "inplace-input"
            }
        }, options, true);

        this.init();
    };

    InplaceEditor.prototype = {
        init: function() {
            this.createEditBtn();
        },

        createEditBtn: function () {
            var edit = $('<a/>')
                .addClass(this.options.editButton.cssClass)
                .attr('title', this.options.title)
                .css('padding-left', '5px')
                .css('cursor', 'pointer')
                .append(
                    $('<i/>').addClass(this.options.editButton.logo.cssClass)
                )
            ;

            edit.click($.proxy(this.textToInput, this));

            // If the field link data is defined, add a link around the field text.
            if (this.fieldLink !== undefined) {
                var textLink = $('<a/>').attr('href', this.fieldLink).attr('target', this.fieldLinkTarget);

                this.field.html(textLink.html(this.text));
            }

            this.field.append(edit);

            this.editBtn = edit;
        },

        textToInput: function () {
            this.createInput();
        },

        createInput: function () {
            var self = this;

            var text = this.field.text();

            var input = $('<input/>')
                .addClass(this.options.input.cssClass)
                .val(text)
            ;

            this.field.html(input).append(this.editBtn);

            input.focusout(function () {
                self.text = $(this).val();
                self.inputToText();
                self.save();
            });

            input.focus();
        },

        save: function() {
            var self = this;
            $.ajax({
                url: self.url,
                data: {
                    text: self.text
                },
                type: 'POST'
            })
        },

        inputToText: function () {
            this.field.html(this.text);

            this.createEditBtn();
        }
    };

    return InplaceEditor;
})();
