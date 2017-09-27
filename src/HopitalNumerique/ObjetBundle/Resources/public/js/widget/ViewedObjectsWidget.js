/**
 * Manages the display of the table of objects in the ViewedObjectsWidget
 */
var ViewedObjectsWidget = (function() {

    ViewedObjectsWidget = function(widget, options) {
        this.widget = widget;
        this.widgetButtons = widget.find('.widget-btn');
        this.widgetTable = widget.find('.widget-table');
        this.deleteButton = widget.find('.delete-consultation-btn');
        this.subscribeButton = widget.find('.subscribe-btn');

        this.rows = $('tbody tr', this.widgetTable);

        this.options = $.extend({
            deleteMessage: "Are you sure you want to delete this consultation?",
            itemsToShow: 10
        }, options, true);

        this.init();
    };

    ViewedObjectsWidget.prototype = {
        init: function() {
            this.initDom();
            this.bindEvents();
        },

        initDom: function() {
            this.rows.slice(this.options.itemsToShow).hide();

            $('.less', this.widgetButtons).hide();
            $('.more', this.widgetButtons)[this.rows.length > this.options.itemsToShow ? "show" : "hide"]();
        },

        bindEvents: function () {
            this.confirmDelete();
            this.subscribe();
            $('.more', this.widgetButtons).click($.proxy(this.showMore, this));
            $('.less', this.widgetButtons).click($.proxy(this.showLess, this));
        },

        showMore: function () {
            this.rows.show();
            $('.less', this.widgetButtons).show();
            $('.more', this.widgetButtons).hide();
        },

        showLess: function () {
            this.rows.slice(this.options.itemsToShow).hide();
            $('.more', this.widgetButtons).show();
            $('.less', this.widgetButtons).hide();
        },

        confirmDelete: function () {
            var self = this;
            this.deleteButton.on('click', function () {
                return confirm(self.options.deleteMessage);
            });
        },

        subscribe: function () {
            this.subscribeButton.on('click', function (evt) {
                evt.preventDefault();
                var self = this;
                $.ajax({
                    url: this.href,
                    method: 'POST',
                    data: {
                        'wanted': this.classList.contains('btn-success')
                    },
                    success: function (data) {
                        self.classList.toggle('btn-success');
                        self.classList.toggle('btn-danger');
                        self.innerHTML = ('unsubscribe' === data) ? self.dataset.active : self.dataset.inactive;
                    }
                });
            })
        }
    };

    return ViewedObjectsWidget;
})();
