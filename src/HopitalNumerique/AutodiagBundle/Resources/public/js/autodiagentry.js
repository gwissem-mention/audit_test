var AutodiagEntry = function(element, entry) {
    this.element = element;
    this.entry = entry;
    this.summary = undefined;
    this.chapters = {};

    this.options = {
        saveDelay: 500
    };

    this.init();
};

AutodiagEntry.prototype = {
    init: function()
    {
        this.initEntry();
        this.initChapters();
        this.initSummary();

        this.bindChapters();
    },

    // Init creation form
    initEntry: function()
    {
        if (this.entry === null && $('#synthesisCreate').length > 0) {
            $(function () {
                $.fancybox({
                    content: $('#synthesisCreate'),
                    minWidth: '80%',
                    afterClose: function() {
                        history.back();
                    }
                });
            });

            // var instance = this;
            // $('form', $('.popin')).submit(function (e) {
            //     var form = $(this);
            //     $.post(form.get(0).action, form.serialize(), function(response) {
            //         instance.entry = response;
            //         $.fancybox.close();
            //     });
            // });
        }
    },

    initChapters: function()
    {
        var instance = this;
        $('.chapter[data-chapter]', this.element).each(function() {
            var id = $(this).data('chapter');
            instance.chapters[id] = new Chapter(id, $(this));
            for (var attribute in instance.chapters[id].attributes) {
                instance.bindAttributeSave(
                    instance.chapters[id].attributes[attribute]
                );
            }
        });

        for (var i in this.chapters) {
            // init hierarchy
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
        this.summary = new Summary($('#summary', this.element), this, {
            previous: $('.navigation .prev', this.element),
            next: $('.navigation .next', this.element)
        });
    },

    bindChapters: function()
    {
        var instance = this;
        for (var i in this.chapters) {
            this.chapters[i].onNotConcerned(function(chapter) {
                $.post(
                    Routing.generate('hopitalnumerique_autodiag_entry_chapter_notconcerned', {
                        entry: instance.entry,
                        chapter: chapter.id
                    })
                );
            });
        }
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
    },

    bindAttributeSave: function(attribute)
    {
        var instance = this;
        attribute.onChange(function () {
            if (attribute.changedTimer !== undefined) {
                clearTimeout(attribute.changedTimer);
            }

            attribute.changedTimer = setTimeout(function() {
                instance.submitAttributeForm(attribute);
            }, instance.options.saveDelay);
        });
    },

    submitAttributeForm: function(attribute)
    {
        var form = attribute.element.find('form');
        if (this.entry !== null) {
            $.ajax({
                url: Routing.generate(
                    'hopitalnumerique_autodiag_entry_attribute_save',
                    {
                        attribute: attribute.id,
                        entry: this.entry
                    }
                ),
                data: form.serialize(),
                method: 'POST',
                dataType: 'json'
            });
        }
    }
};
