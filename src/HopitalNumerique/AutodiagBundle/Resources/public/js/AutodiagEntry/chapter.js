var Chapter = function(id, element)
{
    this.id = id;
    this.element = element;
    this.parent = undefined;
    this.visible = false;
    this.childrens = {};

    this.attributes = {};

    this.callbacks = {
        onCompletionChange: $.Callbacks()
    };

    this.init();
};

Chapter.prototype = {
    init: function()
    {
        this.initAttributes();
    },

    initAttributes: function()
    {
        var instance = this;
        this.element.find('> .attributes .attribute').each(function () {
            var id;
            if ((id = $(this).data('attribute')) !== undefined) {
                instance.attributes[id] = new Attribute(id, $(this));
                instance.bindAttribute(instance.attributes[id]);
            }
        });
    },

    bindAttribute: function(attribute)
    {
        var instance = this;
        attribute.onChange(function() {
            instance.callbacks.onCompletionChange.fire(
                instance.getCompletion()
            );

            instance.parent.callbacks.onCompletionChange.fire(
                instance.parent.getCompletion()
            );
        });
    },

    getCompletion: function()
    {
        var filled = 0,
            total = 0;
        for (var i in this.attributes) {
            total++;
            filled += this.attributes[i].isFilled() ? 1 : 0;
        }

        for (var i in this.childrens) {
            for (var j in this.childrens[i].attributes) {
                total++;
                filled += this.childrens[i].attributes[j].isFilled() ? 1 : 0;
            }
        }


        return Math.round(filled * 100 / total);
    },

    onCompletionChange: function(callback)
    {
        this.callbacks.onCompletionChange.add(callback);
    },

    addChildren: function(chapter)
    {
        this.childrens[chapter.id] = chapter;
    },

    getChildrens: function()
    {
        return this.childrens;
    },

    setParent: function(parent)
    {
        this.parent = parent;
        parent.addChildren(this);
    },

    getParent: function()
    {
        return this.parent;
    },

    getElement: function()
    {
        return this.element;
    },

    hide: function()
    {
        this.visible = false;
        this.element.hide();
    },

    show: function()
    {
        if (this.visible === false) {
            this.element.show();
            this.visible = true;

            if (this.parent !== undefined) {
                this.parent.show();
            }

            for (var i in this.childrens) {
                this.childrens[i].show();
            }
        }
    }
};
