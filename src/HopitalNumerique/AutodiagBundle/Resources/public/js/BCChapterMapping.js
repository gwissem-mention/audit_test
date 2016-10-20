var BCChapterMapping = function() {
    var mapping = {
        7: {
            1: 61,
            2: 62,
            3: 63,
            4: 64,
            5: 83,
            6: 84,
            7: 85,
            8: 86,
            9: 87,
            10: 88,
            11: 89,
            12: 93,
            13: 94
        }
    };


    return {
        map: function (autodiagId) {
            var oldChapterId = window.location.hash.substr(1);
            if (mapping[autodiagId] !== undefined && mapping[autodiagId][oldChapterId] !== undefined) {
                window.location.hash = mapping[autodiagId][oldChapterId];
            }
        }
    };

}();
