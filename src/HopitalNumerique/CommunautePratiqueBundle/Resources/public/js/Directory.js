/**
 * CommunautePratique directory.
 *
 * Directory messages via Ajax query
 */

var Directory;

(function() {
    Directory = function () {
        this.init();
    };

    Directory.prototype = {
        init: function () {
            $('.cdp-directory .directory .directory-user').on('click', function (e) {
                e.preventDefault();

                var $modal = $('#directory-user-details-modal');
                $modal.find('.modal-content').html('');
                $modal.modal();

                $.get($(this).attr('href'), function (response) {
                    $modal.find('.modal-content').html(response);

                    $modal.find('[data-toggle="tooltip"]').tooltip();

                    $modal.find('.ajax-action').on('click', function (e) {
                        e.preventDefault();

                        $.post($(this).attr('href'));

                        $(this).hide();
                    })
                });
            });
        }
    }
})();
