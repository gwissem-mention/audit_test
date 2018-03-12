/**
 * CommunautePratique discussion list.
 *
 * Discussion messages via Ajax query
 */

var Discussion;

(function() {
    Discussion = function (scope, canReorder, preopenNewDiscussionModal) {

        tinyMCE.PluginManager.load('publicationDomaine', '/bundles/hopitalnumeriqueobjet/js/publication/plugin.minByDomaine.js');

        this.createDiscussionMessage();

        if (scope === "editReply") {
            this.initEditReplyForm();

            return;
        } else if (scope === "addDiscussion") {
            this.initEditor('#new-discussion');

            return;
        }

        this.scope = scope;
        this.canReorder = canReorder ? canReorder : false;
        this.$container = $('.discussions');
        this.$list = this.$container.find('.list');
        this.$discussion = this.$container.find('.discussion');
        this.$messages = this.$container.find('.message');
        this.$lazyLoadBtn = this.$list.find('.load-more .more');
        this.$lazyLoadAllBtn = this.$list.find('.load-more .all');

        this.activeDiscussion = {
            element: undefined,
            width: 0,
            left: 0,
            top: undefined,
            pinned: false,
        };

        this.init();

        if (preopenNewDiscussionModal) {
            $('#new-discussion-modal').modal('show');
        }
    };

    Discussion.prototype = {
        initEditReplyForm: function () {
            this.initEditor('.discussions.reply');

            this.bindFileEvent($('.discussions.reply .file-dropzone .files > .file'));
        },

        init: function () {
            var that = this,
                loader,
                discussion = document.querySelector('.discussions');

            that.discussionEvents();
            that.initLazyLoad();

            that.$list.find('.item').on('click', function (e) {
                var scrollToHighlight = $(e.target).hasClass('target-highlight');
                var scrollToNewMessage = $(e.target).hasClass('new-message-badge');

                var $link = $(this);

                e.preventDefault();

                if ($link.hasClass('dragging')) {
                    return;
                }

                loader = that.$container.nodevoLoader().start();

                that.setActiveDiscussion($(this));
                window.history.pushState("", "", $(this).data('url'));

                $.get($(this).attr('href'), function (response) {
                    discussion.dataset.search = 'visualization';

                    loader.finished();
                    that.$discussion.html(response);
                    that.$list.find('.item').removeClass('active');
                    $link.addClass('active');
                    that.discussionEvents();

                    if (scrollToHighlight && that.$discussion.find('.message--helpful').length) {
                        $('html, body').animate({
                            scrollTop: that.$discussion.find('.message--helpful').slice(0, 1).position().top
                        });
                    } else if (scrollToNewMessage && that.$discussion.find('#new-message').length) {
                        $('html, body').animate({
                            scrollTop: that.$discussion.find('#new-message').position().top
                        });
                    } else {
                        $('html, body').animate({
                            scrollTop: 0
                        });
                    }
                })
            });

            document.querySelector('.back-btn').addEventListener('click', function (e) {
                e.preventDefault();
                window.history.pushState("", "", $(this).attr('href'));
                discussion.dataset.search = 'browsing';
            });

            that.setActiveDiscussion(that.$list.find('.item.active'));

            this.initEditor('#new-discussion-modal');
            this.discussionReading();
            this.dragDropEvent();

            $(window).scroll(function (e) {
                if (e.currentTarget.innerWidth > 767) {
                    if ($(this).scrollTop() >= that.activeDiscussion.top) {
                        if (!that.activeDiscussion.pinned) {
                            that.activeDiscussion.pinned = true;

                            that.activeDiscussion.element.css({
                                position: 'fixed',
                                left: that.activeDiscussion.left,
                                top: 0,
                                width: that.activeDiscussion.width,
                                zIndex: 100
                            });
                        }
                    }
                }

                if (undefined !== that.activeDiscussion.top && $(this).scrollTop() < that.activeDiscussion.top) {
                    that.resetActiveTabPosition(that.activeDiscussion.element);
                }
            });
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

            $.post(reorderUri, {'order': JSON.stringify(getTree(that.$list.find('.items')))});
        },

        discussionReading: function ()
        {
            var that = this;
            var scrollTimer;
            $(window).on('scroll', function (e) {
                if (that.scope === 'group' && $('.group .tabs a.tab.discussion.active').length === 0) {
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

            that.$lazyLoadAllBtn.on('click', function (e) {
                e.preventDefault();

                that.$list.find('.item.hidden').removeClass('hidden');

                that.lazyLoadBtnVisibility();
            });
        },

        lazyLoadBtnVisibility: function () {
            if (this.$list.find('.item.hidden').length === 0) {
                this.$lazyLoadBtn.addClass('hidden');
                this.$lazyLoadAllBtn.addClass('hidden');
            }
        },

        initEditor: function (element) {
            var that = this;

            tinyMCE.init({
                entity_encoding : 'raw',
                selector     : element + ' textarea.content',
                theme        : "modern",
                theme_url    : '/bundles/nodevotools/js/tinymce/themes/modern/theme.min.js',
                skin_url     : '/bundles/nodevotools/js/tinymce/skins/lightgray',
                plugins      : 'link publicationDomaine',
                height       : 120,
                menubar      : false,
                content_css  : '/bundles/nodevotools/css/wysiwyg.css',
                toolbar1     : 'bold | underline | italic | link | publicationDomaine',
                relative_urls: false,
                statusbar    : false,
                paste_as_text: true
            });

            $(element + ' .file-dropzone .area i').on('click', function (e) {
                $(this).parents('.area').trigger('click');
            });

            var fileUploadingCounter = 0;

            $(element + ' .file-dropzone .area').dropzone({
                url : $(element + ' .file-dropzone .area').data('upload-uri'),
                createImageThumbnails: false,
                maxFilesize: 10,
                previewTemplate: '<div></div>',
                init: function () {

                    var fileCount = $(element + ' .file-dropzone .files > .file').length;

                    this.on("success", function (file, response) {
                        fileCount++;
                        var $prototype = $(element + ' .file-dropzone .files .prototype .file').clone();
                        $prototype.find('.filename').text(file.name);

                        $prototype.find('.remove').attr('href', Routing.generate('hopitalnumerique_fichier_remove', {file: response.fileId}));

                        $($(element + ' .file-dropzone .files .prototype').data('prototype').replace(/__name__/g, fileCount))
                            .val(response.fileId)
                            .appendTo($prototype)
                        ;

                        $prototype.appendTo($(element + ' .file-dropzone .files'));

                        that.bindFileEvent($prototype);
                    })
                },
                sending: function () {
                    fileUploadingCounter++;

                    that.updateDropzoneState($(this.element), fileUploadingCounter);
                },
                complete: function () {
                    fileUploadingCounter--;

                    that.updateDropzoneState($(this.element), fileUploadingCounter);
                },
                error: function (file, errorMessage, xhr) {
                    if (typeof xhr === 'object' && xhr.hasOwnProperty('status') && -1 !== [401, 403].indexOf(xhr.status)) {
                        window.location.reload();
                        return;
                    }

                    if (typeof errorMessage === 'string') {
                        alert(errorMessage);
                    } else {
                        alert('Une erreur est survenue');
                        console.log(errorMessage);
                    }
                }
            });
        },

        updateDropzoneState: function (dropzone, fileCounter) {
            dropzone.toggleClass('processing', fileCounter > 0);
        },

        bindFileEvent: function ($element) {
            $element.each(function (k, e) {
                $(e).find('.remove').on('click', function (e) {
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

            $('.goto-new-file').on('click', function (e) {
                e.preventDefault();

                $('html, body').animate({
                    scrollTop: $('.discussion .newFile').slice(0, 1).position().top
                });
            });

            $('.discussion-user').on('click', function (e) {
                e.preventDefault();

                var $modal = $('#action-modal');
                $modal.find('.modal-content').html('');
                $modal.modal();

                $.get($(this).attr('href'), function (response) {
                    $modal.find('.modal-content').html(response);

                    $modal.find('[data-toggle="tooltip"]').tooltip();

                    $modal.find('.ajax-action').on('click', function (e) {
                        e.preventDefault();

                        $.post($(this).attr('href'),{}, function () {
                            location.reload();
                        });

                        $(this).hide();
                    })
                });
            });

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

                if ($(this).find('option:selected').data('confirm')) {
                    if (!confirm($(this).find('option:selected').data('confirm'))) {
                        $(this).val('');
                        return;
                    }
                }

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

            $('.message .actions .move').on('click', function (e) {
                var uri = e.target.parentNode.parentNode.dataset.uri,
                    loader = $(this).parents('.message').nodevoLoader().start()
                ;

                $.get(uri, function (response) {
                    $('#action-modal').modal().find('.modal-content').html(response);
                    loader.finished();
                });
            });
        },

        setActiveDiscussion: function ($element) {
            if ($element.length === 0) {
                return;
            }

            undefined !== this.activeDiscussion.element && this.resetActiveTabPosition(this.activeDiscussion.element);
            this.activeDiscussion = {
                element: $element,
                width: $element.outerWidth(),
                top: $element.offset().top,
                left: $element.left,
            };
        },

        resetActiveTabPosition: function ($tab) {
            $tab.css({
                position: 'relative',
                left: 'auto',
                zIndex: 1,
                width: 'auto'
            });
            this.activeDiscussion.pinned = false;
        },

        createDiscussionMessage: function () {
            var createMessageButton = document.querySelector('.save .send'),
                createDiscussionButton = document.querySelector('input.publish')
            ;

            if (createMessageButton) {
                createMessageButton.addEventListener('click', function (event) {
                    event.target.disabled = true;
                    event.target.form.submit();
                });
            }

            if (createDiscussionButton) {
                createDiscussionButton.addEventListener('click', function (event) {
                    event.target.disabled = true;
                    event.target.form.submit();
                });
            }
        }
    }
})();
