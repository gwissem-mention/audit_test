/**
 * Loads the contents of the list with the result of an ajax request
 */
var AjaxList = function(element) {
    this.element = element;

    this.init();
};

AjaxList.prototype = {
    init: function() {
        this.element.select2({
            ajax: {
                url: this.element.data('url'),
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            },
            minimumInputLength: 3,
            width: '100%',
            allowClear: true,
            placeholder: '-'
        });
    }
};
