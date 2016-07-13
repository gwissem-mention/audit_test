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
        var instance = this;
        this.summary = new Summary($('#summary', this.element), this, {
            previous: $('.navigation .prev', this.element),
            next: $('.navigation .next', this.element)
        });
    },

    showChapter: function(id, scrollTo)
    {
        var chapter = this.chapters[id];
        for (var i in this.chapters) {
            this.chapters[i].hide();
        }
        chapter.show();

        if (scrollTo === true || scrollTo === undefined) {
            $('html, body').animate({
                scrollTop: chapter.getElement().offset().top
            }, {
                easing: 'easeOutCubic'
            });
        }
    }
};
