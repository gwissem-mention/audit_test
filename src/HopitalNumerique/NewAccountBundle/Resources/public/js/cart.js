var NewAccountCart;

(function() {
    NewAccountCart = function (reportEditComponent) {
        this.reportEditComponent = reportEditComponent;

        this.init();
    };

    NewAccountCart.prototype = {
        init: function () {

            $('.share-tooltip').tooltip();

            if ($('.cart-items .item').length > 0) {
                $('.cart-items').DataTable({
                    paging: false,
                    searching: false,
                    "columnDefs": [
                        { "orderable": false, "targets": [0, 3] }
                    ]
                });
            }

            this.bindEvents();
        },
        bindEvents: function()
        {
            var component = this;

            $('input.cart-item-checkall').on('change', function(e) {
                component.toggleAllCheckbox($(this).is(':checked'));
            });

            $('input.cart-item-checkbox').on('change', function (e) {
                component.changeAddButtonState();
                component.updateCheckallState();
            });

            $('.cart-add-to-report').on('click', function(e) {
                e.preventDefault();
                component.addObjects($(this))
            });

            $('.cart-items .item .item-remove').on('click', function (e) {
                return confirm($(this).data('confirm'));
            });

            component.reportEditComponent.refreshView(true);
        },
        addObjects: function($btn) {
            var component = this;

            $.post($btn.data('uri'), $('input.cart-item-checkbox:checked'), function(data, responseCode, response) {
                $('input.cart-item-checkbox, input.cart-item-checkall').prop('checked', false);
                $btn.attr('disabled', true);

                component.reportEditComponent.refreshView(false);
            });
        },
        toggleAllCheckbox: function(status) {
            var component = this;

            $('input.cart-item-checkbox').prop('checked', status);
            component.changeAddButtonState();
        },
        changeAddButtonState: function() {
            $('.cart-add-to-report').attr('disabled', $('input.cart-item-checkbox:checked').length === 0);
        },
        updateCheckallState: function() {
            $('input.cart-item-checkall').prop('checked', $('input.cart-item-checkbox:checked').length === $('input.cart-item-checkbox').length)
        }
    };
})();
