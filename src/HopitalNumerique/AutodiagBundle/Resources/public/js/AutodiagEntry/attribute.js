var Attribute = function(id, element)
{
    this.id = id;
    this.element = element;

    this.options = {
        saveDelay: 500
    };

    this.commentEnabled = false;
    this.mood = undefined;
    this.moodIconClass = {
        1: 'fa fa-frown-o fa-2x',
        2: 'fa fa-meh-o fa-2x',
        3: 'fa fa-smile-o fa-2x'
    };

    this.init();
};

Attribute.prototype = {
    init: function()
    {
        this.bindSubmit();
        this.handleComment();
        this.handleMood();
    },

    bindSubmit: function()
    {
        var self = this;
        $('input, select, textarea', this.element).on('change', function() {
            if ($(this).data('changedTimer') !== undefined) {
                clearTimeout($(this).data('changedTimer'));
            }

            var form = $(this).closest('form');
            $(this).data('changedTimer', setTimeout(function() {
                self.submitForm(form);
            }, self.options.saveDelay));
        });
    },

    submitForm: function(form)
    {
        $.post(form.get(0).action, form.serialize(), function() {
            console.log('saved');
        });
    },

    handleComment: function()
    {
        // Show non empty comments
        if ($('form .attribute-comment textarea', this.element).val().length > 0) {
            this.showComment();
        }

        // Bind click on comment tool
        $('.tools .tool-comment', this.element).on('click', $.proxy(this.toggleComment, this));
    },

    toggleComment: function()
    {
        if (this.commentEnabled) {
            this.hideComment();
        } else {
            this.showComment();
        }
    },

    showComment: function()
    {
        this.commentEnabled = true;
        $('form .attribute-comment', this.element).show();
    },

    hideComment: function()
    {
        this.commentEnabled = false;
        var textarea = $('form .attribute-comment textarea', this.element);
        var originalValue = textarea.val();

        $('form .attribute-comment', this.element).hide();

        // Trigger change if value before empty is same as value after empty
        textarea.empty();
        if (textarea.val() != originalValue) {
            textarea.trigger('change');
        }
    },

    handleMood: function()
    {
        var instance = this;
        this.computeMood();
        $('input, select, textarea', this.element).on('change', function() {
            instance.computeMood();
        });
    },

    /**
     * Calcul la coloration
     * @TODO Prendre en compte les valeurs "Non concerné" (= -1 ?)
     */
    computeMood: function()
    {
        var selects = $('.attribute-value *', this.element).filter('select'),
            radios = $('.attribute-value *', this.element).filter('input[type="radio"]'),
            inputs = $('.attribute-value *', this.element).filter('input[type="text"]'),
            min = 1,
            max = 1,
            value = 1;

        if (selects.length > 0) {
            selects.each(function() {
                var selectMin = undefined,
                    selectMax = undefined;
                $(this).find('option').each(function() {
                    if ($(this).val().length > 0) {
                        selectMin = selectMin !== undefined ? Math.min(selectMin, $(this).val()) : $(this).val();
                        selectMax = selectMax !== undefined ? Math.max(selectMax, $(this).val()) : $(this).val();
                    }
                });
                min *= selectMin;
                max *= selectMax;
                if ($(this).val().length > 0) {
                    value *= $(this).val();
                } else {
                    value = null;
                }
            });
        } else if (radios.length > 0) {
            radios.each(function () {
                min = Math.min(min, $(this).val());
                max = Math.max(max, $(this).val());
                value = $(this).prop('checked') ? $(this).val() : value;
            });
        } else if (inputs.length > 0) {
            value = null;
        } else {
            value = null;
        }

        if (null === value) {
            $('.attribute-mood', this.element).hide();
        } else {
            var a =  (max - min) / 100;
            var b = min;
            var x = (value - b) / a;
            var tier = Math.max(1, Math.ceil(x / (100 / 3)));

            $('.attribute-mood', this.element).show().html(
                $('<i />').addClass(
                    this.moodIconClass[tier]
                )
            )
        }
    }
};


