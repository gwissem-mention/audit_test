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
    
});
// Permet la recherche textuelle depuis l'input généré du wysiwyg
function rechercheTexte()
{
	if ($("input#recherche-texte-generate").val().length) {
		window.location.href = ('href', "/recherche-par-referencement/requete-generator/null/" + $("input#recherche-texte-generate").val()  + "/null");
	}

}