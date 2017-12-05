$(document).ready(function () {

    $('.survey-details').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        $.fancybox.open({
            type: 'ajax',
            href: $(this).attr('href'),
            helpers: {
                overlay: {
                    locked: false
                }
            }
        })
    })
});
