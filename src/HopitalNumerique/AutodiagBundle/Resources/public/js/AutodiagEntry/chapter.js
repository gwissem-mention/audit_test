var Chapter = function(id, element)
{
    this.id = id;
    this.element = element;
    this.parent = undefined;
    this.visible = false;
    this.childrens = {};

    this.attributes = {};

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
            }
        });
    },

    getCompletion: function()
    {
        
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
