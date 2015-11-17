/**
 * Classe gérant les barres de progression du nombre de places dans les groupes de la communauté de pratiques.
 */
var CommunautePratique_Groupe_PlaceProgression = function() {};

$(document).ready(function() {
    CommunautePratique_Groupe_PlaceProgression.init();
});

/**
 * @var integer Largeur maximale que peut avoir la barre de progression
 */
CommunautePratique_Groupe_PlaceProgression.PROGRESSION_WIDTH_MAX = null;


/**
 * Initialisation.
 */
CommunautePratique_Groupe_PlaceProgression.init = function()
{
    CommunautePratique_Groupe_PlaceProgression.initProgressionWidthMax();
};

/**
 * Affiche la barre de progression des dates d'un groupe.
 * 
 * @var integer   groupeId                  ID du groupe
 * @var timestamp nombreParticipantsMaximum Nombre max de participants
 * @var timestamp nombreParticipantsActuel  Nombre actuel de participants
 */
CommunautePratique_Groupe_PlaceProgression.displayProgression = function(groupeId, nombreParticipantsMaximum, nombreParticipantsActuel)
{
    if (nombreParticipantsActuel >= nombreParticipantsMaximum)
    {
        CommunautePratique_Groupe_PlaceProgression.setProgressionWidth(groupeId, CommunautePratique_Groupe_PlaceProgression.PROGRESSION_WIDTH_MAX);
    }
    else
    {
        CommunautePratique_Groupe_PlaceProgression.setProgressionWidth(groupeId, nombreParticipantsActuel / nombreParticipantsMaximum * CommunautePratique_Groupe_PlaceProgression.PROGRESSION_WIDTH_MAX);
    }
};

/**
 * Affiche la progression des dates.
 * 
 * @var integer   groupeId                 ID du groupe
 * @var integer width Largeur de la progression en pixel
 */
CommunautePratique_Groupe_PlaceProgression.setProgressionWidth = function(groupeId, width)
{
    $('#places-progression-barre-' + groupeId + ' .contenu').animate({
        width: width + 'px'
    }, {
        duration: 3000,
        easing: 'easeOutQuint'
    });
};

/**
 * Retourn la largeur maximale que peut avoir la progression des dates en pixels.
 * 
 * @return integer Largeur maximale
 */
CommunautePratique_Groupe_PlaceProgression.initProgressionWidthMax = function()
{
    if (null === CommunautePratique_Groupe_PlaceProgression.PROGRESSION_WIDTH_MAX)
    {
        $.each($('.communaute-de-pratiques-panel-groupes .groupe .places-progression'), function(i, element) {
            if (0 != $(element).width())
            {
                CommunautePratique_Groupe_PlaceProgression.PROGRESSION_WIDTH_MAX = parseInt($(element).width());
                return false;
            }
        });
    }
};
