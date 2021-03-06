var Attribute = function(id, element)
{
    this.id = id;
    this.element = element;

    this.callbacks = {
        onChange: $.Callbacks()
    };

    this.moodEnabled = element.data('mood') !== undefined;
    this.moodInversed = element.data('inverse-mood') !== undefined;

    this.commentEnabled = false;

    this.notConcernedValue = -1;
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
        this.bindEvents();
        this.handleComment();
        this.handleMood();
    },

    bindEvents: function()
    {
        var instance = this;
        $('input, select, textarea', this.element).on('change', function () {
            instance.callbacks.onChange.fire();
        });
    },

    onChange: function(callback)
    {
        this.callbacks.onChange.add(callback);
    },

    isFilled: function()
    {
        var fields = $('input[type="text"], select, textarea', this.element.find('.attribute-value'));
        var filled = fields.length > 0;
        fields.each(function () {
            filled = filled && $(this).val().length > 0;
        });

        $('input[type="radio"]', this.element.find('.attribute-value')).each(function() {
            filled = filled || $(this).prop('checked');
        });

        return filled;
    },

    handleComment: function()
    {
        // Show non empty comments
        if ($('form .attribute-comment textarea', this.element).length > 0) {
            if ($('form .attribute-comment textarea', this.element).val().length > 0) {
                this.showComment();
            }

            // Bind click on comment tool
            $('.tools .tool-comment', this.element).on('click', $.proxy(this.toggleComment, this));
        }
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
        textarea.get(0).value = '';
        if (textarea.val() != originalValue) {
            textarea.trigger('change');
        }
    },

    handleMood: function()
    {
        if (this.moodEnabled) {
            var instance = this;
            this.computeMood();
            this.onChange(function () {
                instance.computeMood();
            });
        }
    },

    /**
     * Calcul la coloration
     */
    computeMood: function()
    {
        var instance = this,
            selects = $('.attribute-value *', this.element).filter('select'),
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
                    if ($(this).val().length > 0 && $(this).val() != instance.notConcernedValue) {
                        selectMin = selectMin !== undefined ? Math.min(selectMin, $(this).val()) : $(this).val();
                        selectMax = selectMax !== undefined ? Math.max(selectMax, $(this).val()) : $(this).val();
                    }
                });
                min *= selectMin !== undefined ? selectMin : 1;
                max *= selectMax !== undefined ? selectMax : 1;
                if ($(this).val().length > 0) {
                    value *= $(this).val();
                } else {
                    value = null;
                }
            });
        } else if (radios.length > 0) {
            radios.each(function () {
                if ($(this).val() != instance.notConcernedValue) {
                    min = Math.min(min, $(this).val());
                    max = Math.max(max, $(this).val());
                }
            });
            value = radios.filter(':checked').val() || null;
        } else if (inputs.length > 0) {
            value = null;
        } else {
            value = null;
        }

        if (null === value || value == this.notConcernedValue) {
            $('.attribute-mood', this.element).css('visibility', 'hidden');
        } else {
            var a =  (max - min) / 100;
            var b = min;
            var x = (value - b) / a;
            if (this.moodInversed) {
                x = 100 - x;
            }
            var tier = Math.max(1, Math.ceil(x / (100 / 3)));

            $('.attribute-mood', this.element).css('visibility', 'visible').html(
                $('<i />').addClass(
                    this.moodIconClass[tier]
                )
            )
        }
    },

    isNotconcernedCompliant: function()
    {
        var selects = $('.attribute-value *', this.element).filter('select'),
            radios = $('.attribute-value *', this.element).filter('input[type="radio"]'),
            compliant = false,
            instance = this
        ;

        selects.each(function () {
            $('option', $(this)).each(function () {
                compliant = compliant || ($(this).val() == instance.notConcernedValue);
            });
        });

        radios.each(function () {
            compliant = compliant || ($(this).val() == instance.notConcernedValue);
        });

        return compliant;
    },

    setNotConcerned: function()
    {
        if (this.isNotconcernedCompliant()) {
            var selects = $('.attribute-value *', this.element).filter('select'),
                radios = $('.attribute-value *', this.element).filter('input[type="radio"]'),
                instance = this
            ;

            selects.each(function() {
                $(this).val(instance.notConcernedValue);
            });

            radios.each(function() {
                $(this).prop('checked', $(this).val() == instance.notConcernedValue);
            });

            this.computeMood();

            // Trigger on change
            // this.callbacks.onChange.fire();
        }
    }
};


