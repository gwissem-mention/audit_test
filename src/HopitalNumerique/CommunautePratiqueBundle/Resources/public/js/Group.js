var Group;

(function() {
    Group = function () {
        this.$tabs = $('.group .tabs a.tab');
        this.init();
        this.loadActiveTab();
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

                    var uri = $block.data('content-uri');

                    if ($(e.target).data('filter')) {
                        uri = $block.data('content-uri-'+$(e.target).data('filter'));
                        $(e.target).data('filter', null);
                    }

                    $.get(uri, function (response) {
                        $block.html(response);
                        loader.finished();

                        if ($block.data('cache') === undefined || $block.data('cache') === 'enabled') {
                            $block.data('init', true);
                        }
                    })
                }
            });
        },

        loadActiveTab: function () {
            document.querySelector('.group .tabs a.tab.active').click();
        },

        preOpenDiscussionTab: function () {
            $('.group .tabs a.tab.discussion').trigger('click');
        }
    }
})();
