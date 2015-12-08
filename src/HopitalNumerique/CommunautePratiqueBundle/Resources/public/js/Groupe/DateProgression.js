/**
 * Classe gérant les barres de progression des dates dans les groupes de la communauté de pratiques.
 */
var CommunautePratique_Groupe_DateProgression = function() {};

$(document).ready(function() {
    CommunautePratique_Groupe_DateProgression.init();
});

/**
 * @var integer Largeur maximale que peut avoir la barre de progression des dates
 */
CommunautePratique_Groupe_DateProgression.PROGRESSION_WIDTH_MAX = null;

/**
 * @var integer Marge en pixels entre le début de la barre de progression et le curseur de la date d'inscription (de même entre le curseur de fermeture et la fin de la barre)
 */
CommunautePratique_Groupe_DateProgression.DATE_PROGRESSION_CURSEUR_MARGE = 20;


/**
 * Initialisation.
 */
CommunautePratique_Groupe_DateProgression.init = function()
{
    CommunautePratique_Groupe_DateProgression.initProgressionWidthMax();
};

/**
 * Affiche la barre de progression des dates d'un groupe.
 * 
 * @var integer   groupeId                 ID du groupe
 * @var timestamp dateInscriptionOuverture Date de début des inscriptions
 * @var timestamp dateDemarrage            Date de démarrage
 * @var timestamp dateFin                  Date de fermeture
 */
CommunautePratique_Groupe_DateProgression.displayProgression = function(groupeId, dateInscriptionOuverture, dateDemarrage, dateFin)
{
    var aujourdhui = CommunautePratique_Groupe_DateProgression.getAujourdhuiTimestamp();
    
    if (aujourdhui < dateInscriptionOuverture)
    {
        CommunautePratique_Groupe_DateProgression.setProgressionWidth(groupeId, CommunautePratique_Groupe_DateProgression.DATE_PROGRESSION_CURSEUR_MARGE / 2);
    }
    else if (aujourdhui < dateDemarrage)
    {
        CommunautePratique_Groupe_DateProgression.setProgressionEntreInscriptionEtDemarrage(groupeId, dateInscriptionOuverture, dateDemarrage);
    }
    else if (aujourdhui <= dateFin)
    {
        CommunautePratique_Groupe_DateProgression.setProgressionEntreDemarrageEtFin(groupeId, dateDemarrage, dateFin);
    }
    else if (aujourdhui > dateFin)
    {
        CommunautePratique_Groupe_DateProgression.setProgressionWidth(groupeId, CommunautePratique_Groupe_DateProgression.PROGRESSION_WIDTH_MAX - CommunautePratique_Groupe_DateProgression.DATE_PROGRESSION_CURSEUR_MARGE / 2);
    }
};

/**
 * Affiche la progression si la date du jour est comprise entre la date d'inscription et la date de démarrage.
 * 
 * @var integer   groupeId                 ID du groupe
 * @var timestamp dateInscriptionOuverture Date de début des inscriptions
 * @var timestamp dateDemarrage            Date de démarrage
 */
CommunautePratique_Groupe_DateProgression.setProgressionEntreInscriptionEtDemarrage = function(groupeId, dateInscriptionOuverture, dateDemarrage)
{
    var dateEcartWidth = parseInt( (CommunautePratique_Groupe_DateProgression.PROGRESSION_WIDTH_MAX - 2 * CommunautePratique_Groupe_DateProgression.DATE_PROGRESSION_CURSEUR_MARGE) / 2 );
    var aujourdhui = CommunautePratique_Groupe_DateProgression.getAujourdhuiTimestamp();

    CommunautePratique_Groupe_DateProgression.setProgressionWidth( groupeId, parseInt( ( aujourdhui - dateInscriptionOuverture ) / ( dateDemarrage - dateInscriptionOuverture ) * dateEcartWidth + CommunautePratique_Groupe_DateProgression.DATE_PROGRESSION_CURSEUR_MARGE ) );
};

/**
 * Affiche la progression si la date du jour est comprise entre la date de démarrage et la date de fin.
 * 
 * @var integer   groupeId      ID du groupe
 * @var timestamp dateDemarrage Date de démarrage
 * @var timestamp dateFin       Date de fermeture
 */
CommunautePratique_Groupe_DateProgression.setProgressionEntreDemarrageEtFin = function(groupeId, dateDemarrage, dateFin)
{
    var dateEcartWidth = parseInt( (CommunautePratique_Groupe_DateProgression.PROGRESSION_WIDTH_MAX - 2 * CommunautePratique_Groupe_DateProgression.DATE_PROGRESSION_CURSEUR_MARGE) / 2 );
    var aujourdhui = CommunautePratique_Groupe_DateProgression.getAujourdhuiTimestamp();

    CommunautePratique_Groupe_DateProgression.setProgressionWidth( groupeId, parseInt( ( 1 + ( aujourdhui - dateDemarrage ) / ( dateFin - dateDemarrage ) ) * dateEcartWidth + CommunautePratique_Groupe_DateProgression.DATE_PROGRESSION_CURSEUR_MARGE ) );
};

/**
 * Affiche la progression des dates.
 * 
 * @var integer   groupeId                 ID du groupe
 * @var integer width Largeur de la progression en pixel
 */
CommunautePratique_Groupe_DateProgression.setProgressionWidth = function(groupeId, width)
{
    $('#date-progression-barre-' + groupeId + ' .contenu').animate({
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
CommunautePratique_Groupe_DateProgression.initProgressionWidthMax = function()
{
    if (null === CommunautePratique_Groupe_DateProgression.PROGRESSION_WIDTH_MAX)
    {
        $.each($('.communaute-de-pratiques-groupe .progression .barre'), function(i, element) {
            if (0 != $(element).width())
            {
                CommunautePratique_Groupe_DateProgression.PROGRESSION_WIDTH_MAX = parseInt($(element).width());
                return false;
            }
        });
    }
};

/**
 * Retourne le timestamp du jour à minuit.
 * 
 * @return timestamp Timestamp de minuit
 */
CommunautePratique_Groupe_DateProgression.getAujourdhuiTimestamp = function()
{
    var aujourdhui = new Date();
    aujourdhui.setHours(0);
    aujourdhui.setMinutes(0);
    aujourdhui.setSeconds(0);
    aujourdhui.setMilliseconds(0);

    return (aujourdhui.getTime() / 1000);
};
