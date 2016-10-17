// Pop de confirmation de supression de synthèse
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

// Affiche les syntèses par domaine
$(function(){
    checkSyntesisCount();

    $('#domain-choice').change(function(){
        var $synthesesContent = $('#syntheses-content');

        var loader = $synthesesContent.nodevoLoader().start();

        $.get($(this).find('option:selected').data('url'), null, function(data) {
                loader.finished();
                $synthesesContent.html(data);
                checkSyntesisCount();
            }
        );
    });
});


// Vérification au moins deux synthèses cochées
function checkSyntesisCount() {
    $('#create-synthesis-btn').on('click', function(){
        if ($('#syntheses-form input[type=checkbox]:checked').length < 2) {
            alert($(this).data('errormessage'));

            return false;
        }

        $('body').nodevoLoader().start();
    });
}