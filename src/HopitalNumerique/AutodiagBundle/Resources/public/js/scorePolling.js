var ScorePolling = function (options) {

    this.options = $.extend({}, options);

    this.syntheses = [];

    this.init();
};

ScorePolling.prototype = {
    init: function ()
    {
        var instance = this;
        $('[data-score-polling]').each(function () {
            instance.showLoading($(this));
            instance.syntheses.push($(this).data('score-polling'));
        });

        this.startPolling();
    },

    showLoading: function (el)
    {   el.data('content', el.html());
        el.html('Calcul en cours');
    },

    startPolling: function ()
    {
        if (this.syntheses.length > 0) {
            this.request();
        }
    },

    request: function ()
    {
        console.log('request', this.syntheses);

        var instance = this;
        var xhr = $.ajax({
            url: this.options.url,
            dataType: 'json',
            data: {
                'syntheses': this.syntheses
            }
        });

        xhr.done(function (data) {

            var done = [];
            for (var i in instance.syntheses) {
                var found = false;
                for (var j in data) {
                    if (data[j] == instance.syntheses[i]) {
                        found = true;
                    }
                }

                if (!found) {
                    done.push(instance.syntheses[i]);
                }
            }

            for (var i in done) {
                instance.done(done[i]);

                for (var key in instance.syntheses) {
                    if (instance.syntheses[key] == done[i]) {
                        instance.syntheses.splice(key, 1);
                    }
                }
            }

            console.log(instance.syntheses.length);

            if (instance.syntheses.length > 0) {
                instance.request();
            }
        });
    },

    done: function (id)
    {
        console.log('done');
        var el = $('[data-score-polling="' + id + '"]');
        el.html(el.data('content'));
        el.removeAttr('data-score-polling');
    }
};
