var Group;

(function() {
    Group = function (){
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
            })
        }
    }
})();
