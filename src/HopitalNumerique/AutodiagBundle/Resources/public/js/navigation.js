var AutodiagNavigation = function(element, options) {
    this.element = element;

    this.options = $.extend({
        partialResultsAuthorized: true
    }, options);

    this.restitutionEnabled = false;

    this.init();
};

AutodiagNavigation.prototype = {
    init: function() {
        this.bindEvents();
    },

    setAutodiag: function(autodiag) {
        this.autodiag = autodiag;

        this.bindEntryRelatedEvents();
        this.handleRestitutionLink(autodiag.summary.getGlobalCompletion());
    },

    bindEvents: function() {
        var instance = this;

        $('.restitution a', this.element).click(function (e) {

            e.preventDefault();

            if (false === instance.restitutionEnabled) {
                return false;
            }

            instance.restitutionEnabled = false;

            var xhr = $.ajax({
                url: $(this).attr('href')
            });
            xhr.complete(function(response) {
                instance.restitutionEnabled = true;
                var redirect = response.getResponseHeader('RESTITUTION_REDIRECT');
                console.log(redirect);
                if (null !== redirect) {
                    window.location.href = redirect;
                    return;
                }

                $.fancybox({
                    content: response.responseText
                });
            });
        });
    },

    bindEntryRelatedEvents: function() {
        this.autodiag.summary.onCompletionChange($.proxy(this.handleRestitutionLink, this));
    },

    handleRestitutionLink: function(completion) {
        var active = this.options.partialResultsAuthorized === true || completion == 100;
        this.restitutionEnabled = active;
        if (active) {
            $('.restitution a', this.element).removeClass('disabled');
        } else {
            $('.restitution a', this.element).addClass('disabled');
        }
    }
};
