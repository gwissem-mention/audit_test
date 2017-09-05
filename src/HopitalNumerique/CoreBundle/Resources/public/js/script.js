$(document).ready(function() {
    $('[data-toggle="popover"]').popover({
        html: true,
        content: function() {
            var menu = document.querySelector('ul.account-menu');
            return menu.outerHTML;
        }
    });

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

    $('#menu > li').each(function(){
        if ($(this).find('ul').length > 0) {

            $(this).find('li').on('touchstart', function(e){
                e.stopPropagation();
            });

            $(this).on('touchstart', function() {
                if ($(window).width() < 991) {
                    $(this).siblings().removeClass('touched');
                    $(this).toggleClass('touched');
                }
            });

            $(this).find('> a, > span').on('touchstart', function(e) {
                if ($(window).width() < 991) {
                    e.preventDefault();
                }
            });
        }
    });

    // Form recherche home, modification du href on change
    $("input[name='recherche_textuelle']").change(function() {
        $('a#search-header-home').attr('href', "/recherche-par-referencement/requete-generator/null/" + $(this).val()  + "/null");
        $('a#search-avance-header-home').attr('href', "/recherche-par-referencement?type=avancee");
    });

    $('.fancybox').fancybox({
        autoSize: false,
        width: '80%'
    });

    $('[data-mask]').each(function(i, element) {
        $(element).mask($(element).attr('data-mask'));
    });


    // Form recherche on click
    $("button#search-header-home-generate").click(function() {
    	rechercheTexte();
    });

    // Form recherche on press enter
    $("input#recherche-texte-generate").keypress(function(e) {
        if(e.which == 13) {
        	rechercheTexte();
        }
    });

    if( $('form.toValidate').length > 0) {
        $('form.toValidate').validationEngine();
    }
});
// Permet la recherche textuelle depuis l'input généré du wysiwyg
function rechercheTexte()
{
	if ($("input#recherche-texte-generate").val().length) {
		window.location.href = ('href', "/recherche-par-referencement/requete-generator/null/" + $("input#recherche-texte-generate").val()  + "/null");
	}

}
