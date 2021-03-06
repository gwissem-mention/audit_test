var AutodiagEntry = function(element, entry, options) {
    this.element = element;
    this.entry = entry;
    this.summary = undefined;
    this.chapters = [];

    this.options = $.extend({
        saveDelay: 500,
        attribute_save_error_msg: ''
    }, options);

    this.init();
};

AutodiagEntry.CHAPTER_KEY = 'autodiag.chapter.hash';

AutodiagEntry.prototype = {
    init: function()
    {
        this.initUI();
        this.initEntry();
        this.initChapters();
        this.initSummary();

        this.bindChapters();
    },

    initUI: function()
    {
        $('[data-toggle="tooltip"]').tooltip();
    },

    // Init creation form
    initEntry: function()
    {
        if (this.entry === null && $('#synthesisCreate').length > 0) {

            var chapterHash = window.location.hash;
            if (chapterHash.length > 0) {
                if (typeof Storage !== "undefined") {
                    sessionStorage.setItem(AutodiagEntry.CHAPTER_KEY, chapterHash.substr(1));
                }
            }

            $(function () {
                $.fancybox({
                    content: $('#synthesisCreate'),
                    minWidth: '80%',
                    topRatio: 0.2,
                    closeBtn: false,
                    helpers     : {
                        overlay : {closeClick: false} // prevents closing when clicking OUTSIDE fancybox
                    },
                    keys : {
                        close  : null
                    },
                    afterClose: function() {
                        history.back();
                    }
                });
            });
        }
    },

    initChapters: function()
    {
        var instance = this;
        $('.chapter[data-chapter]', this.element).each(function() {
            var id = $(this).data('chapter');
            var chapter = new Chapter(id, $(this));
            for (var attribute in chapter.attributes) {
                instance.bindAttributeSave(
                    chapter.attributes[attribute]
                );
            }
            instance.chapters.push(chapter);
        });

        for (var i in this.chapters) {
            // init hierarchy
            var chapter = this.chapters[i].getElement();
            while (chapter.parents('.chapter[data-chapter]').length > 0) {
                this.chapters[i].setParent(
                    this.getChapterById(
                        chapter.parents('.chapter[data-chapter]').data('chapter')
                    )
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
        var chapter = this.getChapterById(id);
        for (var i in this.chapters) {
            this.chapters[i].hide();
        }

        if (chapter === undefined) {
            return false;
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
        var instance = this;
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
            }).fail(function () {
                instance.notifyError(instance.options.attribute_save_error_msg)
            });
        }
    },

    getChapterById: function(chapterId)
    {
        for (var i in this.chapters) {
            if (this.chapters[i].id === parseInt(chapterId)) {
                return this.chapters[i];
            }
        }

        return null;
    },

    notifyError: function(message)
    {
        alert(message);
    }
};
