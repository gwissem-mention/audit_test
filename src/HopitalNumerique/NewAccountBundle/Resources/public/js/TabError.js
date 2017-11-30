var TabErrorHandler = function() {
    this.tabs = [];

    this.init();
};

TabErrorHandler.prototype = {
    init: function() {
        this.initTabs();
        this.initValidationEngine();

        this.checkErrors();
        this.showFirstErrorTab();
    },

    initTabs: function() {
        var instance = this;

        $('.tab-pane').each(function() {
            var tab = $('a[data-target="#' + $(this).attr('id') + '"]');

            if (tab.length > 0) {
                instance.tabs.push({
                    tab: tab,
                    selector: $(this),
                    errors: false
                });
            }
        });
    },

    checkErrors: function() {
        var instance = this;

        for (var i in this.tabs) {
            var fields = $('*[class^="validate"]', this.tabs[i].selector);
            fields.each(function() {
                instance.tabs[i].errors = $('.formErrorContent', instance.tabs[i].selector).length > 0;
            });

            this.tabs[i].errors = this.tabs[i].errors || $('.text-danger', this.tabs[i].selector).length > 0;
        }

        this.render();
    },

    initValidationEngine: function() {
        var instance = this;

        $('#my-profile-form').bind("jqv.field.result", function(event, field, errorFound) {
            if (errorFound) {
                for (var i in instance.tabs) {
                    instance.checkErrors();
                }
            }
        });
    },

    render: function() {
        for (var i in this.tabs) {
            if (this.tabs[i].errors) {
                var warning = $('#tab-error-logo').html();
                if (this.tabs[i].tab.html().indexOf(warning) === -1) {
                    this.tabs[i].tab.html(this.tabs[i].tab.html() + warning);
                }
            }
        }
    },

    showFirstErrorTab: function() {
        var show = false;

        for (i = 0; i < this.tabs.length; i++) {
            var tab = this.tabs[i];
            if (tab.errors && !show) {
                tab.tab.tab('show');
                show = true;
            }
        }
    }
};
