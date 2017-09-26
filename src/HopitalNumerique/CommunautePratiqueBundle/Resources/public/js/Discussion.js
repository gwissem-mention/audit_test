/**
 * CommunautePratique discussion list.
 *
 * Discussion messages via Ajax query
 */

var Discussion;

(function() {
    Discussion = function (){
        this.$container = $('.discussions');
        this.$list = this.$container.find('.list');
        this.$discussion = this.$container.find('.discussion');
        this.$messages = this.$container.find('.message');

        this.init();
    };

    Discussion.prototype = {
        init: function () {
            var that = this;

            that.discussionEvents();

            that.$list.find('a').on('click', function (e) {
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

        },

        discussionEvents: function() {

            $('.discussion .actions .discussion-actions').on('change', function (e) {
                var value = $(this).val();

                switch ($(this).find('option:selected').data('action')) {
                    case 'goto':
                        location.href = value;
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
        }
    }
})();
