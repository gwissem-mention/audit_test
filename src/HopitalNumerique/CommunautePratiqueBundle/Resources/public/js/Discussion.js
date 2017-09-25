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

        this.init();
    };

    Discussion.prototype = {
        init: function () {
            var that = this;

            that.$list.find('a').on('click', function (e) {
                var $link = $(this);
                e.preventDefault();

                var loader = that.$discussion.nodevoLoader().start();

                $.get($(this).attr('href'), function (response) {
                    loader.finished();
                    that.$discussion.html(response);
                    that.$list.find('a').removeClass('active');
                    $link.addClass('active');
                })
            })

        }
    }
})();
