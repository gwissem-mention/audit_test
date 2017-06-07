/**
 * Let users edit values in the same place as they are displayed.
 */
var InplaceEditor = (function() {

    InplaceEditor = function(field, url) {
        this.field = field;
        this.url = url;
        this.objectId = this.field.data('id');
        this.text = null;

        this.options = {
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
        };

        this.init();
    };

    InplaceEditor.prototype = {
        init: function() {
            this.createEditBtn();
        },

        createEditBtn: function () {
            var edit = $('<a/>')
                .addClass(this.options.editButton.cssClass)
                .css('padding-left', '5px')
                .css('cursor', 'pointer')
                .append(
                    $('<i/>').addClass(this.options.editButton.logo.cssClass)
                )
            ;

            edit.click($.proxy(this.textToInput, this));

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
                    id: self.objectId,
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
