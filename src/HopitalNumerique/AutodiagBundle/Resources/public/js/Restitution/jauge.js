var Jauge;

(function() {

    var jaugeCount = 0;

    Jauge = function(element, options)
    {
        this.element = element;

        this.options = $.extend({
            bottom: -5,
            delay: true
        }, options);

        this.stacks = [];

        this.init();

        jaugeCount++;
    };

    Jauge.prototype = {
        init: function ()
        {
            this.initScores();
            this.initCompletion();
        },

        initScores: function ()
        {
            var instance = this;

            $('.jauge-widget', this.element).each(function (j) {
                var el = $(this);
                setTimeout(function() {
                    $('[data-value]', el).each(function(i) {
                        var el = $(this);

                        var title = (el.data('label') ? (el.data('label') + ' : ') : '') + el.data('value') + '%';

                        if (el.data('autodiag-entry-name')) {
                            title += " ("+el.data('autodiag-entry-name')+")";
                        }


                        el.tooltip({
                            title: title,
                            container: 'body'
                        });

                        if (el.data('color') !== undefined) {
                            el.css({
                                borderBottomColor: el.data('color')
                            });
                        }

                        setTimeout(function() {
                            var bottom = instance.options.bottom;
                            if (instance.stacks[el.data('value')] !== undefined) {
                                bottom -= (instance.stacks[el.data('value')] * 2);
                                instance.stacks[el.data('value')]++;
                            } else {
                                instance.stacks[el.data('value')] = 1;
                            }

                            el.css({
                                left: el.data('value') + '%',
                                bottom: bottom,
                                zIndex: instance.stacks[el.data('value')]
                            });
                        }, instance.options.delay * i * 50);
                    });
                }, instance.options.delay * ((jaugeCount * 70) + j * 50));
            });
        },

        initCompletion: function()
        {
            $('.completion[data-value]', this.element).each(function (j) {
                var value = $(this).data('value');

                if (value > 0) {
                    if (value < 49) {
                        value = 1;
                    } else if (value < 99) {
                        value = 2;
                    } else {
                        value = 3;
                    }
                }

                $(this)
                    .append(
                        $('<div />')
                            .addClass('completion-bar')
                            .addClass(value >= 1  ? 'active' : null)
                            .css({
                                'height': '30%',
                                'left': '0px'
                            })
                    )
                    .append(
                        $('<div />')
                            .addClass('completion-bar')
                            .addClass(value >= 2  ? 'active' : null)
                            .css({
                                'height': '60%',
                                'left': '6px'
                            })
                    )
                    .append(
                        $('<div />')
                            .addClass('completion-bar')
                            .addClass(value >= 3  ? 'active' : null)
                            .css({
                                'left': '12px'
                            })
                    )
                    .tooltip({
                        container: 'body'
                    });
            });
        }
    };

})();
