var Chapter = function(id, element)
{
    this.id = id;
    this.element = element;
    this.parent = undefined;
    this.visible = false;
    this.childrens = {};

    this.attributes = {};

    this.callbacks = {
        onCompletionChange: $.Callbacks(),
        onNotConcerned: $.Callbacks()
    };

    this.init();
};

Chapter.prototype = {
    init: function()
    {
        this.initAttributes();
        this.handleNotConcerned();
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
            instance.completionChanged();
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
                console.log(filled);
            }
        }

        return Math.floor(filled * 100 / total);
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
    },

    /**
     * Gestion du "Non concerné"
     */
    handleNotConcerned: function()
    {
        var compliant = Object.keys(this.attributes).length > 0;
        for (var i in this.attributes) {
            compliant = compliant && this.attributes[i].isNotconcernedCompliant();
        }

        if (compliant) {
            $('.not-concerned', this.element).show();
        } else {
            $('.not-concerned', this.element).hide();
        }

        $('.not-concerned', this.element).first().on('click', $.proxy(this.setNotConcerned, this));
    },

    setNotConcerned: function()
    {
        for (var i in this.attributes) {
            this.attributes[i].setNotConcerned();
        }

        for (var j in this.childrens) {
            this.childrens[j].setNotConcerned();
        }

        this.completionChanged();
        // this.callbacks.onCompletionChange.fire();
        this.callbacks.onNotConcerned.fire(this);
    },

    onNotConcerned: function(callback)
    {
        this.callbacks.onNotConcerned.add(callback);
    },

    completionChanged: function()
    {
        this.callbacks.onCompletionChange.fire(this.getCompletion());
        if (this.parent !== undefined) {
            this.parent.completionChanged();
        }
    }
};
