var RiskAnalysis;

(function() {

    RiskAnalysis = function () {
        this.$risksContainer = $('#guided-search-step-risks');

        if (this.$risksContainer.length) {
            this.init();
        }
    };

    RiskAnalysis.prototype = {
        init: function () {
            var component = this;

            component.$risksContainer.find('[data-toggle="tooltip"]').tooltip({
                container: 'body'
            });

            $('.guided-search-toolbar .btn').tooltip();

            component.bindSaveEvent();
            component.buildSlider();
            component.bindCommentModalEvent();
            component.bindExcludedObjectsModalEvent();
            component.updateAnalyseBtnState();

            component.$risksContainer.find('tbody .risk-analysis').each(function (k, e) {
                component.updateCriticalityFlag($(e));
                component.updateCommentFlag($(e));
            })
        },

        updateAnalyseBtnState: function() {
            var component = this;

            if (component.isRiskAnalysisProvided()) {
                $('.guided-search-step-analyze').removeClass('disabled');
            } else {
                $('.guided-search-step-analyze').addClass('disabled');
            }
        },

        isRiskAnalysisProvided: function () {
            var component = this;
            var result = false;

            component.$risksContainer.find('select.probability, select.impact').each (function (k, e) {
                var $options = $(e).find('option:selected').filter(function() {
                    return this.value !== "";
                });

                if ($options.length) {
                    result = true;
                }
            });

            return result;
        },

        bindCommentModalEvent: function () {
            var component = this;

            component.$risksContainer.find('.comment-btn').on('click', function (e) {
                e.preventDefault();

                var $line = $(this).parents('tr');

                var $modal = $('.risk-analysis-modal');
                $modal.modal();
                $modal.find('.modal-title').text($line.find('.nature').text() + ' - ' + $line.find('.risk').text());
                $modal.find('.modal-body').html($line.find('.comment-modal-content').html());
                $modal.find('.save').addClass('save-comment');

                $modal.find('.save-comment').one('click', function (e) {
                    e.preventDefault();

                    $line.find('.comment').val($modal.find('.comment-modal').val());
                    $line.find('.comment-modal').text($modal.find('.comment-modal').val());

                    component.saveRisk($line, function () {
                        $modal.modal('hide');
                        component.updateCommentFlag($line);
                        $modal.find('.save').removeClass('save-comment');
                    });
                });
            });
        },

        bindExcludedObjectsModalEvent: function () {
            var component = this;

            component.$risksContainer.find('.excluded-objects-btn').on('click', function (e) {
                e.preventDefault();

                var $line = $(this).parents('tr');

                var $modal = $('.risk-analysis-modal');
                $modal.modal();
                $modal.find('.modal-title').text($line.find('.nature').text() + ' - ' + $line.find('.risk').text());
                $modal.find('.modal-body').html($line.find('.excluded-objects-modal-content').html());
                $modal.find('.save').addClass('save-excluded-objects');

                $modal.find('.save-excluded-objects').one('click', function (e) {
                    e.preventDefault();

                    $modal.find('.object-excluded').each (function (k, e) {
                        $line.find('.object-excluded#'+ $(e).attr('id'))
                            .attr('checked', $(e).prop('checked'))
                            .prop('checked', $(e).prop('checked'))
                        ;
                    });

                    component.saveRisk($line, function () {
                        $modal.modal('hide');
                    });
                });
            });
        },

        updateCommentFlag: function($line) {
            var $btn = $line.find('.comment-btn');

            if ($btn.length === 0) {
                return;
            }

            if ($line.find('.comment').val().length) {
                $btn.addClass('btn-success').removeClass('btn-default');
            } else {
                $btn.removeClass('btn-success').addClass('btn-default');
            }
        },

        buildSlider: function () {
            var component = this;

            component.$risksContainer.find('.skillsRate').slider({
                min: 0,
                max: 100,
                step: 10,
                create: function () {
                    $(this).slider('value', $(this).siblings('.skillsRateValue').val());
                },
                slide: function(event, ui) {
                    $(this).siblings('.skillsRateValue').val(ui.value);
                    $(this).siblings('.skillsRateLabel').text(ui.value + " %");
                },
                stop: function (event, ui) {
                    component.saveRisk($(this).parents('tr'))
                }
            });
        },

        bindSaveEvent: function () {
            var component = this;

            component.$risksContainer.find('select.probability, select.impact').on('change', function (e) {
                var $line = $(this).parents('.risk-analysis');
                component.updateRiskCriticality($line);
                component.saveRisk($line);
                component.updateAnalyseBtnState();
            });
        },
        saveRisk: function ($line, callback) {
            $.post($line.data('analysis-uri'), $line.find('select, input').serialize(), function (a, b, c) {
                if (callback !== undefined) {
                    callback();
                }
            })
        },
        updateRiskCriticality: function ($line) {
            var criticality = $line.find('select.probability').val() * $line.find('select.impact').val();

            if (isNaN(criticality)) {
                criticality = 0;
            }

            $line.find('.criticality').text(criticality).data('value', criticality);

            this.updateCriticalityFlag($line);
        },
        updateCriticalityFlag: function ($line) {
            var $cell = $line.find('.criticality');
            var value = $cell.data('value');

            $cell.removeClass('high').removeClass('medium').removeClass('low').removeClass('none').text(value);

            if (value >= 12) {
                $cell.addClass('high');
            } else if (value >= 8) {
                $cell.addClass('medium');
            } else if (value >= 3) {
                $cell.addClass('low');
            } else if (value > 0) {
                $cell.addClass('none');
            } else {
                $cell.text('');
            }
        }
    };


    $(document).ready(function () {
        new RiskAnalysis();
    });

})();
