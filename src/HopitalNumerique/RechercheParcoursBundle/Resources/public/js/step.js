var GuidedSearchStep;

(function() {

    GuidedSearchStep = function () {
        this.$container = $('#recherche-par-parcours-details');

        if (this.$container.length) {
            this.init();
        }
    };

    GuidedSearchStep.prototype = {
        init: function () {
            var component = this;

            if (window.location.hash) {
                component.$container.find('.type-tab a[href="'+window.location.hash+'"]').tab('show');
                window.location.hash = "";
            }
        }
    };


    $(document).ready(function () {
        new GuidedSearchStep();
    });

})();
