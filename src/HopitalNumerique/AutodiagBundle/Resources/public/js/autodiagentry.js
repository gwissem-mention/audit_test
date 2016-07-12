var AutodiagEntry = function(element) {
    this.element = element;
    this.summary = undefined;
    this.chapters = {};

    // this.options = {
    //     saveDelay: 500
    // };

    this.init();
};

AutodiagEntry.prototype = {
    init: function()
    {
        this.initChapters();
        this.initSummary();
        // this.bindFormSubmit();

        console.log(this.chapters);
    },

    initChapters: function()
    {
        var instance = this;
        $('.chapter[data-chapter]', this.element).each(function() {
            var id = $(this).data('chapter');
            instance.chapters[id] = new Chapter(id, $(this));
        });

        for (var i in this.chapters) {
            var chapter = this.chapters[i].getElement();
            while (chapter.parents('.chapter[data-chapter]').length > 0) {
                this.chapters[i].setParent(
                    this.chapters[chapter.parents('.chapter[data-chapter]').data('chapter')]
                );
                chapter = chapter.parents('.chapter[data-chapter]');
            }
        }
    },

    initSummary: function()
    {
        this.summary = new Summary($('#summary', this.element), this);
    },

    // bindFormSubmit: function()
    // {
    //     var self = this;
    //     $('input, select, textarea', this.element).on('change', function() {
    //         if ($(this).data('changedTimer') !== undefined) {
    //             clearTimeout($(this).data('changedTimer'));
    //         }
    //
    //         var form = $(this).closest('form');
    //         $(this).data('changedTimer', setTimeout(function() {
    //             self.submitForm(form);
    //         }, self.options.saveDelay));
    //     });
    // },
    //
    // submitForm: function(form)
    // {
    //     $.post(form.get(0).action, form.serialize(), function() {
    //         console.log('saved');
    //     });
    // },

    showChapter: function(id)
    {
        var chapter = this.chapters[id];
        for (var i in this.chapters) {
            this.chapters[i].hide();
        }
        chapter.show();

        if (chapter.getParent() !== undefined) {
            $('html, body').animate({
                scrollTop: chapter.getElement().offset().top
            }, {
                easing: 'easeOutCubic'
            });
        }
    }
};
