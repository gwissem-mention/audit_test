/**
 * Loads the list of counties according to the selected region
 */
var CountyList = (function() {

    CountyList = function(regions, counties) {
        this.regions = regions;
        this.counties = counties;

        this.load();

        var countyId = 0;

        if (null !== this.counties.val()) {
            countyId = this.counties.val();
        }

        if (0 !== countyId) {
            this.counties.val(countyId);
        }

        var self = this;

        this.regions.on('change', function () {
            self.counties.val(null);
            self.load();
        });
    };

    CountyList.prototype = {
        /**
         * Dynamically displays the list of counties
         */
        load: function() {
            var self = this;
            $.ajax({
                url: this.regions.data('county-url'),
                data: {
                    id: this.regions.val()
                },
                type: 'POST',
                success: function (data) {
                    var value = self.counties.val();
                    self.counties.html(data);
                    $('option[value="' + value + '"]', self.counties).prop('selected', true);
                }
            })
        }
    };

    return CountyList;
})();
