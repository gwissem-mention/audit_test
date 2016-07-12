var Summary = function(element, autodiag) {
    this.element = element;
    this.autodiag = autodiag;

    this.init();
};

Summary.prototype = {
    init: function()
    {
        this.selectCurrent();
        this.bindEvents();
    },

    bindEvents: function()
    {
        $('.title', this.element).on('click', {instance: this}, this.onTitleSelection);
    },

    onTitleSelection: function(event)
    {
        var instance = event.data.instance;
        var chapter = $(this).closest('li').data('chapter');
        if (chapter !== undefined) {
            instance.selectChapter(chapter);
        }
    },

    selectChapter: function(chapterId)
    {
        this.autodiag.showChapter(chapterId);
        window.location.hash = this.getHashByChapter(chapterId);

        $('li', this.element).removeClass('active');

        var chapter = $('[data-chapter="' + chapterId + '"]', this.element);
        do {
            chapter.addClass('active');
            chapter = chapter.parents('[data-chapter]');
            console.log(chapter);
        } while (chapter.length > 0);
    },

    selectCurrent: function()
    {
        var chapter = window.location.hash.substr(1).length > 0
            ? this.getChapterByHash(window.location.hash.substr(1))
            : $('li', this.element).first().data('chapter');

        this.selectChapter(chapter);
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
