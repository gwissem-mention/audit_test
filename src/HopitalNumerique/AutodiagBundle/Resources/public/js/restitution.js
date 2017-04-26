var AutodiagRestitution = function(element, options) {
    this.element = element;

    this.options = $.extend({
    }, options);

    this.handlers = {};
    this.init();
};

AutodiagRestitution.prototype = {
    init: function()
    {
        $('.compare-score-variation').each(function() {
            new Variation($(this));
        })
    },

    initItems: function ()
    {
        var instance = this;
        $('[data-item-type]', this.element).each(function() {
            var type = $(this).data('item-type');

            if (instance.handlers[type] !== undefined && window[instance.handlers[type].class] !== undefined) {
                new window[instance.handlers[type].class]($(this), instance.handlers[type].arguments);
            }
        });
    },

    addHandler: function (type, handlerClass, arguments)
    {
        this.handlers[type] = {
            class: handlerClass,
            arguments : arguments
        };
    }

};
