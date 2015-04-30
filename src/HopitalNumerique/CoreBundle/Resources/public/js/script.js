$(window).load(function() {
    if($(window).width() > 1000) {
        resize_header();
    }
});

$(window).resize(function() {
    if($(window).width() > 1000) {
        resize_header();
    }
});

$(document).ready(function() {
    $('#mesrequetes.closed').click(function(){
        //on ouvre
        if ( $(this).hasClass('closed') ){
            wrapper = $('<div />').addClass('lock-screen');
            $('body').append(wrapper);
        //on ferme
        }else{
            $('.lock-screen').remove();
        }
        
        $('#mesrequetes').toggleClass('closed open');
        $('#mesrequetes .content').toggle();
    });

    $("#menu-select").change(function() {
        window.location = $(this).find("option:selected").val();
    });

    // Form recherche home, modification du href on change
    $("input[name='recherche_textuelle']").change(function() {
        $('a#search-header-home').attr('href', "/recherche-par-referencement/requete-generator/null/" + $(this).val()  + "/null");
        $('a#search-avance-header-home').attr('href', "/recherche-par-referencement?type=avancee");
    });
});

/*Recalcule la taille du block header slider en fonction de la taille de la fenêtre*/
function resize_header() {
    $('#slide .slick-slide').height($(window).height()-$('#header').outerHeight()-$('#menu-container').outerHeight()-$('#search-help').outerHeight());
    $('#block-fil-discussion').outerHeight($(window).height()-$('#header').outerHeight()-$('#menu-container').outerHeight()-$('#block-chiffres-cles').outerHeight()-$('#block-last-publications').outerHeight()-$('#block-carte-france').height());
}
