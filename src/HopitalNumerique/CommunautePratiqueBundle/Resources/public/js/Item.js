var Item;

(function() {
    Item = function () {
        this.$item = $('.item a.item-modal');
        this.$modal = $('#item-modal');
        this.init();
    };

    Item.prototype = {
        init: function () {
            var that = this;
            that.$item.on('click', function (e) {
                e.preventDefault();

                that.$modal.find('.modal-content').html('');
                that.$modal.modal();

                $.get($(this).attr('href'), function (response) {
                    that.$modal.find('.modal-content').html(response);

                    that.$modal.find('[data-toggle="tooltip"]').tooltip();

                    that.$modal.find('.ajax-action').on('click', function (e) {
                        e.preventDefault();

                        $.post($(this).attr('href'), {}, function () {
                            location.reload();
                        });

                        $(this).hide();
                    })
                });
            });
        }
    }
})();
