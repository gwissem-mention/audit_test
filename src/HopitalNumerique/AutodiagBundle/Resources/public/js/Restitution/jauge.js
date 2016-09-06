var Jauge;

(function() {

    var jaugeCount = 0;

    Jauge = function(element, options)
    {
        this.element = element;

        this.options = options;

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
            $('.jauge-widget', this.element).each(function (j) {
                var el = $(this);
                setTimeout(function() {
                    $('[data-value]', el).each(function(i) {
                        var el = $(this);

                        el.tooltip({
                            title: (el.data('label') ? (el.data('label') + ' : ') : '') + el.data('value') + '%'
                        });
                        setTimeout(function() {
                            el.css('left', el.data('value') + '%');
                        }, i * 50);
                    });
                }, (jaugeCount * 500) + j * 50);
            });
        },

        initCompletion: function()
        {
            $('.completion[data-value]', this.element).each(function (j) {
                var value = $(this).data('value');

                if (value < 49) {
                    value = 1;
                } else if (value < 99) {
                    value = 2;
                } else {
                    value = 3;
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
                    .tooltip();
            });
        }
    };

})();
