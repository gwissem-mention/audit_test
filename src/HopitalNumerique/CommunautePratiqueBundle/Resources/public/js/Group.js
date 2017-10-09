var Group;

(function() {
    Group = function () {
        this.$tabs = $('.group .tabs a.tab');

        this.init();
    };

    Group.prototype = {
        init: function () {
            var that = this;

            that.$tabs.on('click', function (e) {
                e.preventDefault();
                that.$tabs.removeClass('active');

                $(this).tab('show').addClass('active');
            });

            that.$tabs.on('show.bs.tab', function (e) {
                var $block = $('.group').find($(e.target).attr('href'));

                if ($(e.target).hasClass('ajax') && $block.data('init') === false) {
                    var loader = $block.nodevoLoader().start();

                    $.get($block.data('content-uri'), function (response) {
                        $block.html(response);
                        loader.finished();
                        $block.data('init', true);
                    })
                }
            })
        },

        preOpenDiscussionTab: function () {
            $('.group .tabs a.tab.discussion').trigger('click');
        }
    }
})();
