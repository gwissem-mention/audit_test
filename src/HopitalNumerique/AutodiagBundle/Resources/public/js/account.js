$(function () {
    $('#syntheses-content .delete-synthesis').click(function (e) {
        var el = $(this);
        e.preventDefault();
        apprise(
            'Êtes-vous sûr de vouloir supprimer ?',
            {
                confirm: true,
                textOk: 'Oui',
                textCancel: 'Non'
            },
            function (r) {
                if (r) {
                    window.location.href = el.attr('href');
                }
            }
        );
    });
});
