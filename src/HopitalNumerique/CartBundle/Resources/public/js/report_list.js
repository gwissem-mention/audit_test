var ReportList;

(function () {
    ReportList = function (reportEditComponent) {
        this.reportEditComponent = reportEditComponent;
        this.$modal = $('#report-modal');
        this.detailsModalTemplate = Handlebars.compile($('#details-modal-prototype').html());
        this.shareModalTemplate = Handlebars.compile($('#share-modal-prototype').html());
        this.sendModalTemplate = Handlebars.compile($('#send-modal-prototype').html());

        this.init();
    };

    ReportList.prototype = {
        init: function () {

            if ($('.report-list .report-list-line').length > 0) {
                $('.report-list').DataTable({
                    paging: false,
                    searching: false,
                    "columnDefs": [
                        { "orderable": false, "targets": [4] }
                    ],
                    "order": [[2, 'desc']]
                });
            }

            this.bindEvents();
        },
        bindEvents: function () {
            var component = this;

            $('.report-list .report-list-line').each(function (k, e) {
                component.bindReportEvents($(e));
            });
        },
        bindReportEvents: function ($reportLine) {
            var component = this;

            $reportLine.find('.report-details').on('click', function (e) {
                e.preventDefault();
                component.showReportDetailsModal($(this));
            });

            $reportLine.find('.report-share').on('click', function (e) {
                e.preventDefault();
                component.showReportShareModal($(this));
            });

            $reportLine.find('.report-send').on('click', function (e) {
                e.preventDefault();
                component.showSendReportModal($(this));
            });

            $reportLine.find('.report-edit').on('click', function (e) {
                e.preventDefault();

                component.reportEditComponent.setWrapperUri($(this).attr('href'));
                $('.cart-add-to-report').data('uri', $(this).data('add-do-report-uri'))
            });

            $reportLine.find('.report-remove').on('click', function (e) {
                return confirm($(this).data('confirm'));
            })
        },
        showReportShareModal: function ($e) {
            var component = this;
            var reportId = $e.parents('.report-list-line').data('report-id');

            var modalData = {
                'reportTitle': $e.parents('.report-list-line').find('.report-list-line-title').text().trim(),
                'duplicateReportUri': Routing.generate('hopital_numerique_cart_report_duplicate', {'report': reportId}),
                'shareReportUri': Routing.generate('hopital_numerique_cart_report_share', {'report': reportId}),
                'copyReportUri': Routing.generate('hopital_numerique_cart_report_copy', {'report': reportId})
            };

            component.$modal.modal();
            component.$modal.find('.modal-content').html(
                component.shareModalTemplate(modalData)
            );

            $.get($e.prop('href'), {}, function(data) {
                for (var type in data) {

                    modalData[type+'Shares'] = [];

                    for (var key in data[type]) {
                        var share = data[type][key];

                        if (key === "ownerFullName") {
                            modalData[type+'Shares'].push({
                                'target': share
                            });
                        } else {
                            modalData[type+'Shares'].push({
                                'target': share.target,
                                'removeUri': Routing.generate('hopital_numerique_cart_report_remove_sharing', {'reportSharing': share.id})
                            });
                        }
                    }
                }

                component.$modal.find('.modal-content').html(
                    component.shareModalTemplate(modalData)
                );

                component.bindShareReportModalEvents();
            });
        },
        showSendReportModal: function ($e) {
            var component = this;

            var modalData = {
                'reportTitle': $e.parents('.report-list-line').find('.report-list-line-title').text().trim(),
                'formAction': $e.prop('href')
            };

            component.$modal.modal();
            component.$modal.find('.modal-content').html(
                component.sendModalTemplate(modalData)
            );

            component.$modal.find('form').prop('action', modalData.formAction);

            var $formInputs = component.$modal.find('input[type="text"], input[type="email"], textarea');
            var isFormValid = function () {
                var hasEmptyField = false;
                $formInputs.each(function(k, e) {
                    if ($(e).val().trim().length === 0) {
                        hasEmptyField = true;

                        return false;
                    }
                });

                return !hasEmptyField;
            };

            var changeButtonState = function () {
                component.$modal.find('form button[type="submit"]').prop('disabled', !isFormValid());
            };

            $formInputs.on('change', function (e) {
                changeButtonState();
            }).on('keyup', function (e) {
                changeButtonState();
            });

            component.$modal.find('form').on('submit', function (e) {
                if (!isFormValid()) {
                    e.preventDefault();
                }
            });

            component.$modal.find('form').validationEngine();

            changeButtonState();
        },
        bindShareReportModalEvents: function () {
            var component = this;

            component.bindShareSubFormEvents(component.$modal.find('.duplicate'));
            component.bindShareSubFormEvents(component.$modal.find('.share'));
            component.bindShareSubFormEvents(component.$modal.find('.copy'));
        },
        bindShareSubFormEvents: function ($block) {
            var $formInput = $block.find('form input[type="text"], form input[type="email"]');
            
            var isDuplicateFormValid = function () {
                return $formInput.val() !== "";
            };

            var changeButtonState = function () {
                $block.find('form input[type="submit"]').prop('disabled', !isDuplicateFormValid());
            };

            $formInput.on('change', function (e) {
                changeButtonState();
            }).on('keyup', function (e) {
                changeButtonState();
            });

            $block.find('form').on('submit', function (e) {
                if (!isDuplicateFormValid()) {
                    e.preventDefault();
                }
            });

            $block.find('form').validationEngine();

            changeButtonState();
        },
        showReportDetailsModal: function($e) {
            var component = this;

            var modalData = {
                'reportTitle': $e.text().trim(),
                'reportItems': []
            };

            component.$modal.modal();
            component.$modal.find('.modal-content').html(
                component.detailsModalTemplate(modalData)
            );

            $.get($e.prop('href'), null, function(data) {

                for (var k in data) {
                    var item = data[k];

                    title = item.title;
                    if (item.parentsTitle !== null) {
                        title = item.parentsTitle.join(' > ') + ' > ' + title;
                    }

                    modalData.reportItems.push({
                        'title': title,
                        'objectTypeName': item.objectTypeName
                    });
                }

                component.$modal.find('.modal-content').html(
                    component.detailsModalTemplate(modalData)
                );
            });
        }
    };
})();

