var Variation;

(function() {
    Variation = function(element)
    {
        this.element = element;

        this.init();
    };

    Variation.prototype = {
        init: function ()
        {
            this.updateOrientation();
        },

        updateOrientation: function() {
            var variation = this.element.data('variation');

            var arrow = $('<div />').addClass('variation-arrow');
            this.element.append(arrow);

            var degree = -90/100 * variation;
            arrow.css(
                'transform',
                'rotate(' + degree + 'deg)'
            );

            // var canvas = $('<canvas />');
            // canvas.appendTo(this.element);
            //
            // var opts = {
            //     lines: 12, // The number of lines to draw
            //     angle: 0.15, // The length of each line
            //     lineWidth: 0.44, // The line thickness
            //     pointer: {
            //         length: 0.9, // The radius of the inner circle
            //         strokeWidth: 0.035 // The rotation offset
            //     },
            //     colorStart: '#6FADCF',   // Colors
            //     colorStop: '#8FC0DA',    // just experiment with them
            //     strokeColor: '#E0E0E0',   // to see which ones work best for you
            //     percentColors: [[0.0, "#f2dede" ], [0.50, "#fcf8e3"], [1.0, "#dff0d8"]]
            // };
            //
            // var gauge = new Gauge(canvas.get(0)).setOptions(opts);
            // gauge.maxValue = 200;
            // gauge.set(100 + variation);

        }
    };

})();
