var Summary = function(element, autodiag, options) {
    this.element = element;
    this.autodiag = autodiag;

    this.options = $.extend({
        previous: undefined,
        next: undefined
    }, options);

    this.elementOffsetTop = this.element.offset().top;

    this.callbacks = {
        onCompletionChange: $.Callbacks()
    };

    this.chapters = [];
    this.currentIndex = 0;
    this.globalCompletion = 0;

    this.init();
};

Summary.prototype = {
    init: function()
    {
        this.initChapters();
        this.initNavigation();
        this.selectCurrent();
        this.bindEvents();
        this.handleCompletion();
    },

    initChapters: function()
    {
        var chapters = this.autodiag.chapters;
        for (var i in chapters) {
            if (chapters[i].getParent() === undefined) {
                this.chapters.push(chapters[i]);
            }
        }
    },

    initNavigation: function()
    {
        var instance = this;
        if (this.options.previous !== undefined) {
            this.options.previous.on('click', function() {
                instance.previous();
            });
        }

        if (this.options.next !== undefined) {
            this.options.next.on('click', function() {
                instance.next();
            });
        }
    },

    bindEvents: function()
    {
        $('.title', this.element).on('click', {instance: this}, this.onTitleSelection);
        $(window).scroll($.proxy(this.onWindowScroll, this));
    },

    onWindowScroll: function()
    {
        this.element.css({
            paddingTop: $(window).scrollTop() + 20 < this.elementOffsetTop ? 0 : ($(window).scrollTop() - this.elementOffsetTop) + 20
        });
    },

    handleCompletion: function()
    {
        var instance = this;
        $('.progression .value', this.element).each(function() {
            var chapterId = $(this).closest('li').data('chapter');
            var current = $(this);
            $(this).html(
                instance.getCompletionHtml(instance.autodiag.chapters[chapterId].getCompletion())
            );

            instance.autodiag.chapters[chapterId].onCompletionChange(function (completion) {
                current.html(instance.getCompletionHtml(completion));
                instance.handleGlobalCompletion();
            })
        });

        this.handleGlobalCompletion();
    },

    handleGlobalCompletion: function()
    {
        var filled = 0,
            total = 0;
        for (var index in this.autodiag.chapters) {
            var chapter = this.autodiag.chapters[index];
            for (var i in chapter.attributes) {
                total++;
                filled += chapter.attributes[i].isFilled() ? 1 : 0;
            }
        }
        var completion = Math.round(filled * 100 / total);
        this.globalCompletion = completion;

        this.callbacks.onCompletionChange.fire(
            completion
        );

        $('.progress-bar', this.element)
            .css({
                width: completion + '%'
            })
            .html(Math.round(completion) + '%')
            .attr(
                'class',
                completion < 100
                    ? completion < 50
                    ? 'progress-bar progress-bar-danger'
                    : 'progress-bar progress-bar-warning'
                    : 'progress-bar progress-bar-success'
            )
        ;
    },

    getGlobalCompletion: function()
    {
        return this.globalCompletion;
    },

    onCompletionChange: function(callback)
    {
        this.callbacks.onCompletionChange.add(callback);
    },

    getCompletionHtml: function(completion)
    {
        if (completion < 100) {
            var content = completion + '%';

            if (completion > 50) {
                return '<div class="orange">' + content + '</div>';
            }

            return '<div class="red">' + content + '</div>';
        }

        return '<i class="fa fa-check"></i>';
    },

    onTitleSelection: function(event)
    {
        var instance = event.data.instance;
        var chapter = $(this).closest('li').data('chapter');
        if (chapter !== undefined) {
            instance.selectChapter(chapter);
        }
    },

    selectChapter: function(chapterId, scrollTo)
    {
        this.autodiag.showChapter(chapterId, scrollTo);

        window.location.hash = this.getHashByChapter(chapterId);

        $('li.summary-item', this.element).removeClass('active');

        var chapter = $('[data-chapter="' + chapterId + '"]', this.element);
        var parentId = undefined;
        do {
            chapter.addClass('active');
            parentId = chapter.data('chapter');
            chapter = chapter.parents('[data-chapter]');
        } while (chapter.length > 0);

        for (var i in this.chapters) {
            if (this.chapters[i].id == parentId) {
                this.setCurrentIndex(i);
            }
        }
    },

    selectCurrent: function()
    {
        var chapter = window.location.hash.substr(1).length > 0
            ? this.getChapterByHash(window.location.hash.substr(1))
            : $('li.summary-item', this.element).first().data('chapter');

        this.selectChapter(chapter, false);
    },

    setCurrentIndex: function(index)
    {
        index = parseInt(index);
        if (this.options.previous !== undefined) {
            if (index <= 0) {
                this.options.previous.hide();
            } else {
                this.options.previous.show();
            }
        }


        if (this.options.next !== undefined) {
            if (index >= (this.chapters.length - 1)) {
                this.options.next.hide();
            } else {
                this.options.next.show();
            }
        }


        this.currentIndex = index;
    },

    previous: function()
    {
        if (this.currentIndex > 0) {
            this.setCurrentIndex(this.currentIndex - 1);
            this.selectChapter(
                this.chapters[this.currentIndex].id
            );
        }
    },

    next: function()
    {
        if (this.currentIndex < this.chapters.length) {
            this.setCurrentIndex(this.currentIndex + 1);
            this.selectChapter(
                this.chapters[this.currentIndex].id
            );
        }
    },

    /**
     * Get Hash by Chapter ID
     *
     * @param chapter
     * @returns {*}
     */
    getHashByChapter: function(chapter)
    {
        return chapter;
    },

    /**
     * Get chapter ID by hash
     * @param hash
     * @returns {*}
     */
    getChapterByHash: function(hash)
    {
        return hash;
    }
};
