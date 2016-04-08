/**
 * Gestion du glossaire.
 */
var Hn_ReferenceBundle_Glossaire = function() {};

$(document).ready(function () {
    Hn_ReferenceBundle_Glossaire.init();
});

/**
 * Initialisation.
 */
Hn_ReferenceBundle_Glossaire.init = function ()
{
    Hn_ReferenceBundle_Glossaire.initEvents();
};

/**
 * Initialisation des événements.
 */
Hn_ReferenceBundle_Glossaire.initEvents = function ()
{
    $('#glossaire-recherche-form').submit(function (event) {
        var searchedWord = $('#glossaire-recherche').val().trim();
        if ('' !== searchedWord) {
            Hn_ReferenceBundle_Glossaire.searchWordInGlossaire(searchedWord);
        }
        event.preventDefault();
    });
};


/**
 * Recherche un mot à l'intérieurdu glossaire.
 *
 * @param string word Mot à rechercher
 */
Hn_ReferenceBundle_Glossaire.searchWordInGlossaire = function(word)
{
    var highlightOptions = {
        wordsOnly: false,
        caseSensitive: false
    };

    $('.glossaire .elements dl').unhighlight(highlightOptions);
    $('.glossaire .elements dl').highlight(word, highlightOptions);

    if (0 === Hn_ReferenceBundle_Glossaire.getFoundWordCount()) {
        alert('Le mot recherché n\'a pas été trouvé.');
    } else {
        $('html, body').animate({
            scrollTop: $('.glossaire .elements dl .highlight:first').offset().top
        });
    }
};

/**
 * Recherche un mot à l'intérieurdu glossaire.
 *
 * @param string word Mot à rechercher
 */
Hn_ReferenceBundle_Glossaire.getFoundWordCount = function()
{
    return ($('.glossaire .elements dl .highlight').size());
};
