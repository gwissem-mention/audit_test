/**
 * Gestion de la recherche Exalead.
 */
var Hn_RechercheBundle_Referencement_Filter_Exalead = function() {};


$(document).ready(function() {
    Hn_RechercheBundle_Referencement_Filter_Exalead.init();
});


Hn_RechercheBundle_Referencement_Filter_Exalead.init = function()
{
    Hn_RechercheBundle_Referencement_Filter_Exalead.initEvents();
};

Hn_RechercheBundle_Referencement_Filter_Exalead.initEvents = function()
{
    $('#recherche_textuelle').keyup(function(e) {
        //var searchedText = Hn_RechercheBundle_Referencement_Filter_Exalead.getSearchedText();
        //$('#search-text-button').prop('disabled', searchedText.length < 2);
        var canSearch = Hn_RechercheBundle_Referencement_Filter_Exalead.processSearchValidating();
        Hn_RechercheBundle_Referencement.processFilterButtonsActivating();
        Hn_RechercheBundle_Referencement_Filter_Category.processFilterDisplaying();

        if (canSearch && e.keyCode == 13) {
            Hn_RechercheBundle_Referencement.displayResults();
        }
    });
};


/**
 * Affiche / cache la popin de recherche avancée.
 */
Hn_RechercheBundle_Referencement_Filter_Exalead.toggleParametersDisplaying = function()
{
    $('.recherche_textuelle_avancee').slideToggle(200);
};

/**
 * Ajoute un paramètre.
 *
 * @param string  chaineRechercheAvancee Chaîne à ajouter dans le champ
 * @param integer texteSelectionDebut    Position où commencer à sélectionner le texte
 * @param integer texteSelectionFin      Position où terminer à sélectionner le texte (à partir de la fin donc -1 si juste avant le dernier caractère)
 */
Hn_RechercheBundle_Referencement_Filter_Exalead.addParameter = function(parameter, selectionStart, selectionEnd)
{
    var texteRecherche = Hn_RechercheBundle_Referencement_Filter_Exalead.getSearchedText();
    var positionInitiale = texteRecherche.length;

    if (texteRecherche.length > 0) {
        texteRecherche += ' ';
        positionInitiale++;
    }
    texteRecherche += parameter;

    $('#recherche_textuelle').val(texteRecherche);

    document.getElementById('recherche_textuelle').focus();
    document.getElementById('recherche_textuelle').setSelectionRange(positionInitiale + selectionStart, positionInitiale + parameter.length + selectionEnd);
};

/**
 * Retourne le texte recherché.
 *
 * @return string Texte recherché
 */
Hn_RechercheBundle_Referencement_Filter_Exalead.getSearchedText = function()
{
    var searchedText = $('#recherche_textuelle').val();

    if (searchedText.length > 1) {
        return searchedText;
    }

    return '';
};

/**
 * Spécifie le texte recherché.
 *
 * @return string Texte recherché
 */
Hn_RechercheBundle_Referencement_Filter_Exalead.setSearchedText = function(text)
{
    return $('#recherche_textuelle').val(text);
};

/**
 * Retourne si une recherche avancée est saisie.
 *
 * @return boolean Si recherche
 */
Hn_RechercheBundle_Referencement_Filter_Exalead.hasSearch = function()
{
    return ('' != Hn_RechercheBundle_Referencement_Filter_Exalead.getSearchedText());
};

/**
 * Vérifie la validité de la recherche et retourne si valide.
 *
 * @return boolean Si valide
 */
Hn_RechercheBundle_Referencement_Filter_Exalead.processSearchValidating = function()
{
    var searchedText = Hn_RechercheBundle_Referencement_Filter_Exalead.getSearchedText();
    var canSearch = (
        searchedText.length > 1
        || (Hn_RechercheBundle_Referencement.getChosenElements().size() > 0)
    );

    $('#search-text-button').prop('disabled', !canSearch);

    return (searchedText.length > 1);
};

Hn_RechercheBundle_Referencement_Filter_Exalead.highlightWords = function(words)
{
    var highlightOptions = {
        wordsOnly: false,
        caseSensitive: false
    };

    $('.results-group-bloc .result').unhighlight(highlightOptions);
    $('.results-group-bloc .result').highlight(words, highlightOptions);
};
