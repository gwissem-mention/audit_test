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

    },

    initItems: function ()
    {
        var instance = this;
        $('[data-item-type]', this.element).each(function() {
            var type = $(this).data('item-type');

            if (instance.handlers[type] !== undefined && window[instance.handlers[type]] !== undefined) {
                new window[instance.handlers[type]]($(this));
            }
        });
    },

    addHandler: function (type, handlerClass)
    {
        this.handlers[type] = handlerClass;
    }

};
