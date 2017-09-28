/**
 * CommunautePratique discussion list.
 *
 * Discussion messages via Ajax query
 */

var Discussion;

(function() {
    Discussion = function (scope){
        this.scope = scope;
        this.$container = $('.discussions');
        this.$list = this.$container.find('.list');
        this.$discussion = this.$container.find('.discussion');
        this.$messages = this.$container.find('.message');
        this.$lazyLoadBtn = this.$list.find('.load-more');

        this.init();
    };

    Discussion.prototype = {
        init: function () {
            var that = this;

            that.discussionEvents();
            that.initLazyLoad();

            that.$list.find('a.item').on('click', function (e) {
                var $link = $(this);
                e.preventDefault();

                var loader = that.$discussion.nodevoLoader().start();

                window.history.pushState("", "", $(this).data('url'));

                $.get($(this).attr('href'), function (response) {
                    loader.finished();
                    that.$discussion.html(response);
                    that.$list.find('a').removeClass('active');
                    $link.addClass('active');
                    that.discussionEvents();
                })
            });

            this.initEditor('#new-discussion-modal textarea');
        },

        initLazyLoad: function () {
            var that = this;

            that.lazyLoadBtnVisibility();

            that.$lazyLoadBtn.on('click', function (e) {
                e.preventDefault();

                that.$list.find('.item.hidden').slice(0, that.$list.data('lazyload-step')).removeClass('hidden');

                that.lazyLoadBtnVisibility();
            });
        },

        lazyLoadBtnVisibility: function () {
            if (this.$list.find('.item.hidden').length === 0) {
                this.$lazyLoadBtn.addClass('hidden');
            }
        },

        initEditor: function (element) {
            tinyMCE.init({
                entity_encoding : 'raw',
                selector     : element,
                theme        : "modern",
                theme_url    : '/bundles/nodevotools/js/tinymce/themes/modern/theme.min.js',
                skin_url     : '/bundles/nodevotools/js/tinymce/skins/lightgray',
                plugins      : 'paste link',
                height       : 120,
                menubar      : false,
                content_css  : '/bundles/nodevotools/css/wysiwyg.css',
                toolbar1     : 'bold | underline | italic | link',
                relative_urls: false,
                statusbar    : false,
                paste_as_text: true
            });
        },

        discussionEvents: function() {
            this.initEditor('.discussions .discussion textarea.content');

            $('.discussion .select2').select2();

            $('.discussion .actions .discussion-actions').on('change', function (e) {
                var value = $(this).val();

                switch ($(this).find('option:selected').data('action')) {
                    case 'goto':
                        location.href = value;
                        break;
                    case 'modal':
                        var loader = $(this).parents('.actions-block').nodevoLoader().start();
                        $.get(value, function (response) {
                            $('#action-modal').modal().find('.modal-content').html(response);
                            loader.finished();
                        });
                        break;
                }
            });

            $('.discussion .message').find('.helpful').on('click', function (e) {
                var $link = $(this);
                e.preventDefault();

                var loader = $link.nodevoLoader().start();

                $.post($(this).attr('href'), function (response, status) {
                    if (status === "success") {
                        $link.toggleClass('active');
                    }

                    loader.finished();
                });
            });

            if ($('.discussion-domains select').length) {
                $('.discussion-domains select').on('change', function (e) {
                    var $form = $(this).parents('form');


                    if ($form.find('select option:selected:enabled').length) {
                        var loader = $form.nodevoLoader().start();

                        $.post($form.attr('action'), $form.serialize(), function () {
                            loader.finished();
                        });
                    }
                });
            }

            var scrollTimer;
            $(window).on('scroll', function (e) {
                if ($('.group .tabs a.tab.discussion.active').length === 0) {
                    return;
                }

                clearTimeout(scrollTimer);
                scrollTimer = setTimeout(function () {
                    var windowBottom = $(this).scrollTop()+$(window).height();

                    var messages = [];
                    $('.discussions .discussion .messages .message.new').each(function (k, e) {
                        if (($(e).offset().top + $(e).height()) < windowBottom) {
                            messages.push(e);
                        }
                    });

                    if (messages.length) {
                        messages.slice(-1).forEach(function (e) {
                            var messageId = $(e).data('message-id');

                            $.post(
                                $('.discussions .discussion .messages').data('read-message-uri'),
                                {'messageId': messageId}
                            );
                        });
                    }

                }, 1000);
            });
        }
    }
})();
