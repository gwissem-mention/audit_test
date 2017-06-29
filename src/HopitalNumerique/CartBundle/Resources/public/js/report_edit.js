var ReportEdit;

(function () {
    ReportEdit = function ($form) {
        this.$form = $form;
        this.$wrapper = $('.new-report');
        this.$formWrapper = $form.find('.form-wrapper');
        this.$factoryItemsWrapper = $('.new-report-items');

        this.itemLineTemplate = Handlebars.compile(this.$form.find('.new-report-prototype').html());
        this.formTemplate = Handlebars.compile(this.$form.find('.form-prototype').html());

        this.init();
    };

    ReportEdit.prototype = {
        init: function () {
            this.bindEvents();
        },
        bindEvents: function () {
            var component = this;

            $('.new-report .item-line').each(function (k, e) {
                component.bindItemEvent($(e));
            });

            component.$wrapper.sortable({
                handle: ".draggable",
                items: ".item-line",
                update: function(event, ui) {
                    component.reorderItems();
                }
            });
        },
        bindFormEvents: function () {
            var component = this;
            var $input = component.$formWrapper.find('input[name="report[name]"]');

            $input.on('keyup', function () {
                component.updateButtonState();
            });

            $input.on('change', function () {
                component.updateButtonState();
            });
        },
        setWrapperUri: function (uri) {
            var component = this;

            component.$wrapper.data('uri', uri);
            component.refreshView(true);
        },
        isFormFilled: function () {
            var component = this;
            return component.$formWrapper.find('input[name="report[name]"]').val() !== "" && component.$factoryItemsWrapper.find('.item-line').length !== 0;
        },
        updateButtonState: function() {
            var component = this;
            component.$formWrapper.find('input[name="report[submit]"]').prop('disabled', !component.isFormFilled());
        },
        reorderItems: function() {
            var component = this;

            var data = [];
            component.$factoryItemsWrapper.find('.item-line').each(function(k, e) {
                data.push($(e).data('id'));
            });

            $.post(component.$factoryItemsWrapper.data('reorder-uri'), {'itemsOrder': data});
        },
        refreshView: function(updateForm) {
            var component = this;

            $.get(component.$wrapper.data('uri'), null, function(reportFactory) {
                if (reportFactory.factoryItems.length > 0) {
                    $('.new-report-items .alert').addClass('hidden');
                }

                component.$factoryItemsWrapper.attr('data-reorder-uri', Routing.generate('hopital_numerique_cart_report_item_reorder', {'reportFactory': reportFactory.id}));
                component.$factoryItemsWrapper.find('.item-line').remove();

                for (var key in reportFactory.factoryItems) {
                    var item = reportFactory.factoryItems[key];
                    title = item.title;
                    if (item.parentTitle !== null) {
                        title = item.parentTitle + ' > ' + title;
                    }

                    var itemLine = component.itemLineTemplate({
                        'id': item.itemId,
                        'objectTypeName': item.objectTypeName,
                        'title': title,
                        'removeUri': Routing.generate('hopital_numerique_cart_report_item_remove_item', {'reportItem': item.itemId})
                    });

                    $('.new-report-items').append(itemLine);
                    component.bindItemEvent($('.new-report-items .item-line[data-id='+item.itemId+']'));

                }

                if (updateForm) {
                    component.updateForm(reportFactory);
                }

                component.updateButtonState();
            });
        },
        updateForm: function (reportFactory) {
            var component = this;

            var viewData = {
                'form_action': Routing.generate('hopital_numerique_cart_report_factory_edit', {'reportFactory': reportFactory.id}),
                'report_name': '',
                'report_id': false,
                'report_columns': reportFactory.columns
            };

            if (reportFactory.report !== null) {
                viewData.report_name = reportFactory.report.name;
                viewData.report_id = reportFactory.report.id;
            }

            component.$formWrapper.html(component.formTemplate(viewData));
            component.bindFormEvents();
        },
        bindItemEvent: function ($e) {
            var component = this;
            component.bindRemoveItemEvent($e);
        },
        bindRemoveItemEvent: function ($e) {
            var component = this;

            $e.find('.item-line-action-remove').on('click', function (e) {
                e.preventDefault();

                $.ajax({
                    url: $(this).attr('href'),
                    type: 'DELETE',
                    success: function () {
                        $e.slideUp(500, function () {
                            $e.remove();

                            component.noItemAlert();
                            component.updateButtonState();
                        })
                    }
                });
            });
        },
        noItemAlert: function () {
            var component = this;

            if (component.$form.find('.new-report-items .item-line').length === 0) {
                component.$form.find('.new-report-items .alert').removeClass('hidden');
            }
        }
    };
})();

