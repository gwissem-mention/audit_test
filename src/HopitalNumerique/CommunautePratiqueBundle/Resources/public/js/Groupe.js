/**
 * Classe gérant les groupes de la communauté de pratique.
 */
var CommunautePratique_Groupe = function () {
};


$(document).ready(function () {
    CommunautePratique_Groupe.init();

    var url = window.location.pathname;
    $("div.row > div.col-md-3 > a").each(function (i) {
        if ($(this).attr("href") == url) {
            $(this).find('span').addClass("btn-sommaire");
        } else if (url.indexOf('fiche') > -1 && $(this).find('span').text().indexOf('thématique') > -1) {
            $(this).find('span').addClass("btn-sommaire");
        } else if (url.indexOf('user') > -1 && $(this).find('span').text().indexOf('Annuaire') > -1) {
            $(this).find('span').addClass("btn-sommaire");
        }
    });
    if (window.location.href.indexOf('#') > -1) {
        $(".nav-tabs li").removeClass('active');
        $(".nav-tabs li").last().addClass('active');
        $(".tab-content div").removeClass('active');
        $("#documents").addClass('active');
    }

    $(".nav-tabs li a").on('shown.bs.tab', function () {
        CommunautePratique_Groupe.fixeHauteurBlocs();
    })
});


/**
 * Initialisation.
 */
CommunautePratique_Groupe.init = function () {
    // Timeout car parfois, la hauteur du bloc "Mes groupes en cours" n'est pas la bonne au chargement
    setTimeout(function () {
        CommunautePratique_Groupe.fixeHauteurBlocs();
    }, 500);
};

/**
 * Fixe la hauteur des blocs de la page Groupes de travail.
 */
CommunautePratique_Groupe.fixeHauteurBlocs = function () {

    if (1 == $('#communaute-pratiques-groupes').size()) { // Vérifie que l'on est sur la bonne page
        if (parseInt($('body').width()) >= 768) { // Vérifie que l'on n'est pas sur un petit écran
            $('.tab-content').css('height', 'auto');
            $('.communaute-de-pratiques-bloc-mes-groupes').css('height', 'auto');

            var blocGroupesCourantsContenuHeight = 0;
            $.each($('.tab-pane.active'), function (i, element) {
                var height = $(element).outerHeight();
                height += parseInt($('.tab-content').css('paddingTop')) + parseInt($('.tab-content').css('paddingBottom'));
                if (height > blocGroupesCourantsContenuHeight) {
                    blocGroupesCourantsContenuHeight = height;
                }
            });

            var blocGroupesCourantsOngletsHeight = $('.nav-tabs').outerHeight();
            var blocMesGroupesHeight = $('.communaute-de-pratiques-bloc-mes-groupes').outerHeight() + parseInt($('.communaute-de-pratiques-bloc-mes-groupes').css('marginTop')) + parseInt($('.communaute-de-pratiques-bloc-mes-groupes').css('marginBottom')) + parseInt($('.communaute-de-pratiques-bloc-mes-groupes').css('paddingTop')) + parseInt($('.communaute-de-pratiques-bloc-mes-groupes').css('paddingBottom'));
            var blocGroupePublicationsHeight = $('.communaute-de-pratiques-bloc-publications').outerHeight();

            var colonneGaucheHeight = blocGroupesCourantsContenuHeight + blocGroupesCourantsOngletsHeight;
            var colonneDroiteHeight = blocMesGroupesHeight + blocGroupePublicationsHeight;

            if (colonneGaucheHeight < colonneDroiteHeight) {
                $('.tab-content').height(colonneDroiteHeight - blocGroupesCourantsOngletsHeight - parseInt($('.tab-content').css('paddingTop')) - parseInt($('.tab-content').css('paddingBottom')));
            } else if (colonneGaucheHeight > colonneDroiteHeight) {
                $('.tab-content').height(blocGroupesCourantsContenuHeight - parseInt($('.tab-content').css('paddingTop')) - parseInt($('.tab-content').css('paddingBottom')));
                $('.communaute-de-pratiques-bloc-mes-groupes').height(colonneGaucheHeight - blocGroupePublicationsHeight - parseInt($('.communaute-de-pratiques-bloc-mes-groupes').css('marginTop')) - parseInt($('.communaute-de-pratiques-bloc-mes-groupes').css('marginBottom')));
            }
        } else {
            $('.communaute-de-pratiques-bloc-mes-groupes').css({marginTop: '15px'});
        }
    }
};
