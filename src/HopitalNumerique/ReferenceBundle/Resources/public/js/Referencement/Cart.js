$(function(){
    var cartMessage = function(cssClass, message) {
        $.fancybox({
            content: $('<div class="alert alert-block nalert-'+cssClass+'">' + message + '</div>'),
            autoSize: false,
            autoHeight: true,
            width: 700
        });
    };

    $(document).on('search-results-updated', function() {
        $('a.fancybox-ajax:not(.processed)').click(function () {
            $.ajax({
                url: $(this).attr('href'),
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    cartMessage('success', data.message);
                },
                error: function (jqXHR) {
                    cartMessage('danger', jqXHR.responseJSON.message);
                }
            });
            return false;
        }).addClass('processed');
    });
});