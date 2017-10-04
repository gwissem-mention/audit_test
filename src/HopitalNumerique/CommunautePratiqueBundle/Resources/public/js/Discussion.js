/**
 * CommunautePratique discussion list.
 *
 * Discussion messages via Ajax query
 */

var Discussion;

(function() {
    Discussion = function (scope, canReorder) {

        tinyMCE.PluginManager.load('publicationDomaine', '/bundles/hopitalnumeriqueobjet/js/publication/plugin.minByDomaine.js');

        if (scope === "editReply") {
            this.initEditReplyForm();

            return;
        }

        this.scope = scope;
        this.canReorder = canReorder ? canReorder : false;
        this.$container = $('.discussions');
        this.$list = this.$container.find('.list');
        this.$discussion = this.$container.find('.discussion');
        this.$messages = this.$container.find('.message');
        this.$lazyLoadBtn = this.$list.find('.load-more');

        this.init();
    };

    Discussion.prototype = {
        initEditReplyForm: function () {
            this.initEditor('.discussions.reply');

            this.bindFileEvent($('.discussions.reply .file-dropzone .files > .file'));
        },

        init: function () {
            var that = this;

            that.discussionEvents();
            that.initLazyLoad();

            that.$list.find('.item').on('click', function (e) {
                var $link = $(this);
                e.preventDefault();

                if ($link.hasClass('dragging')) {
                    return;
                }

                var loader = that.$discussion.nodevoLoader().start();

                window.history.pushState("", "", $(this).data('url'));

                $.get($(this).attr('href'), function (response) {
                    loader.finished();
                    that.$discussion.html(response);
                    that.$list.find('.item').removeClass('active');
                    $link.addClass('active');
                    that.discussionEvents();
                })
            });

            this.initEditor('#new-discussion-modal');
            this.discussionReading();
            this.dragDropEvent();
        },

        dragDropEvent: function ()
        {
            var that = this;

            if (that.canReorder && that.scope !== "group") {
                return;
            }

            that.dropItemEvent();
            that.dragItemEvent();

            that.$discussion.find('.droppable-layer').droppable({
                drop: function (e, ui) {
                    that.$discussion.removeClass('dragging');
                    that.dragDropResetDiscussion($(ui.draggable).parent('.item-block'));

                    that.sortDiscussionsTitle(that.$list.children('.items'), 1);
                    that.saveDiscussionOrder();
                    that.dropItemEvent();
                    that.dragItemEvent();
                }
            });
        },

        dragItemEvent: function ()
        {
            var that = this;
            var $items = that.$list.find('.item-block .item');

            if ($items.hasClass('ui-draggable')) {
                $items.draggable('destroy');
            }

            $items.draggable({
                revert: true,
                start: function () {
                    $(this).addClass('dragging');

                    if ($(this).parents('.item-block').attr('data-level') > 1) {
                        that.$discussion.addClass('dragging');
                    }
                },
                stop: function () {
                    $(this).removeClass('dragging');
                    that.$discussion.removeClass('dragging');
                }
            });
        },

        dropItemEvent: function ()
        {
            var that = this;
            var $items = that.$list.find('.item-block[data-level="1"] > .item, .item-block[data-level="2"] > .item');

            if (that.$list.find('.ui-droppable').hasClass('ui-droppable')) {
                that.$list.find('.ui-droppable').droppable('destroy');
            }

            $items.droppable({
                over: function () {
                    $(this).addClass('droppable-over');
                },
                out: function () {
                    $(this).removeClass('droppable-over');
                },
                drop: function (e, ui) {
                    $(this).removeClass('droppable-over');
                    var $block = $(this).parent('.item-block');

                    if (
                        $block.attr('data-level') < 3 &&
                        !$(ui.draggable).parent('.item-block').has($block).length &&
                        $(ui.draggable).parent('.item-block').find('.children').has('.item-block').length + $block.attr('data-level') < 3
                    ) {
                        $(ui.draggable).parent('.item-block').prependTo($block.children('.children'));

                        that.sortDiscussionsTitle(that.$list.children('.items'), 1);
                        that.saveDiscussionOrder();
                        that.dropItemEvent();
                        that.dragItemEvent();
                    }
                }
            });
        },

        dragDropResetDiscussion: function ($element)
        {
            $element.attr('data-level', 1);
            $element.prependTo(this.$list.children('.items'));
        },

        sortDiscussionsTitle: function ($parent, level)
        {
            var that = this;

            if ($parent.children('.item-block').length === 0) {
                return;
            }

            $parent.children('.item-block').attr('data-level', level).sort(function (a, b) {
                return +a.getAttribute('data-global-position') - +b.getAttribute('data-global-position');
            }).prependTo($parent);

            if ($parent.find('.item-block[data-level='+level+'] > .children > .item-block').length > 0) {
                $parent.find('.item-block[data-level='+level+'] > .children').has('.item-block').each(function (k, e) {
                    that.sortDiscussionsTitle($(e), level + 1);
                })
            }
        },

        saveDiscussionOrder: function ()
        {
            var that = this;
            var reorderUri = that.$list.data('reorder-uri');

            var getTree = function ($parent) {
                var data = {};

                $parent.children('.item-block').each (function (k, e) {
                    data[$(e).children('.item').data('discussion-id')] = getTree($(e).children('.children'));
                });

                return data;
            };

            $.post(reorderUri, {'order': JSON.stringify(getTree(that.$list))});
        },

        discussionReading: function ()
        {
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
                            $(e).removeClass('new');

                            $.post(
                                $('.discussions .discussion .messages').data('read-message-uri'),
                                {'messageId': messageId}
                            );
                        });
                    }

                }, 1000);
            });
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
            var that = this;

            tinyMCE.init({
                entity_encoding : 'raw',
                selector     : element + ' textarea',
                theme        : "modern",
                theme_url    : '/bundles/nodevotools/js/tinymce/themes/modern/theme.min.js',
                skin_url     : '/bundles/nodevotools/js/tinymce/skins/lightgray',
                plugins      : 'paste link publicationDomaine',
                height       : 120,
                menubar      : false,
                content_css  : '/bundles/nodevotools/css/wysiwyg.css',
                toolbar1     : 'bold | underline | italic | link | publicationDomaine',
                relative_urls: false,
                statusbar    : false,
                paste_as_text: true
            });

            $(element + ' .file-dropzone .area').dropzone({
                url : $(element + ' .file-dropzone .area').data('upload-uri'),
                createImageThumbnails: false,
                previewTemplate: '<div></div>',
                init: function () {

                    var fileCount = $(element + ' .file-dropzone .files > .file').length;

                    this.on("success", function (file, response) {
                        fileCount++;
                        var $prototype = $(element + ' .file-dropzone .files .prototype .file').clone();
                        $prototype.find('.filename').text(file.name);

                        $prototype.find('.insert').attr('href', Routing.generate('hopitalnumerique_fichier_view', {file: response.fileId}));
                        $prototype.find('.remove').attr('href', Routing.generate('hopitalnumerique_fichier_remove', {file: response.fileId}));

                        $($(element + ' .file-dropzone .files .prototype').data('prototype').replace(/__name__/g, fileCount))
                            .val(response.fileId)
                            .appendTo($prototype)
                        ;

                        $prototype.appendTo($(element + ' .file-dropzone .files'));

                        that.bindFileEvent($prototype);
                    })
                }
            });
        },

        bindFileEvent: function ($element) {

            $element.each(function (k, e) {
                var $e = $(e);
                $e.find('.insert').on('click', function (e) {
                    e.preventDefault();

                    tinymce.activeEditor.execCommand('mceInsertContent', false, " <a href='"+$(this).attr('href')+"' target='_blank'>"+$e.find('.filename').text()+"</a> ");
                });

                $e.find('.remove').on('click', function (e) {
                    var that = this;
                    e.preventDefault();

                    $.ajax({
                        url: $(this).attr('href'),
                        type: 'DELETE',
                        success: function () {
                            $(that).parents('.file').remove();
                        }
                    });
                });
            });
        },

        discussionEvents: function() {
            this.initEditor('.discussions .discussion');

            $('[data-toggle="tooltip"]').tooltip();

            $('.discussion-files .filename').on('click', function (e) {
                e.preventDefault();

                $('html, body').animate({
                    scrollTop: $('.messages .message[data-message-id='+$(this).data('message-id')+']').offset().top
                });
            });

            $('.discussion .select2').select2();

            if ($('.open-popin-referencement').length) {
                $('.open-popin-referencement').on('click', function (e) {
                    e.preventDefault();

                    $.fancybox.open({
                        padding: 0,
                        autoSize: false,
                        width: '80%',
                        scrolling: 'auto',
                        modal: true,
                        href: $(this).attr('href'),
                        type: 'ajax'
                    });
                });
            }

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
        }
    }
})();
