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
                if ($(e.target).attr('href') === "#discussion" && $('.group #discussion').data('init') === false) {

                    var loader = $('.group #discussion').nodevoLoader().start();

                    $.get($('.group #discussion').data('content-uri'), function (response) {
                        $('.group #discussion').html(response);
                        loader.finished();
                        $('.group #discussion').data('init', true);
                    })
                }
            })
        },

        preOpenDiscussionTab: function () {
            $('.group .tabs a.tab.discussion').trigger('click');
        }
    }
})();
