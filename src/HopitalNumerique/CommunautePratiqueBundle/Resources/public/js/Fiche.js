/**
 * Classe gérant les fiches de la communauté de pratique.
 */
var CommunautePratique_Fiche = function() {};

/**
 * Ouvre une fiche.
 * 
 * @param integer ficheId ID de la fiche
 */
CommunautePratique_Fiche.open = function(ficheId)
{
    CommunautePratique_Fiche.changeResolution(ficheId, false);
};

/**
 * Ferme une fiche.
 * 
 * @param integer ficheId ID de la fiche
 */
CommunautePratique_Fiche.close = function(ficheId)
{
    CommunautePratique_Fiche.changeResolution(ficheId, true);
};

/**
 * Modifie la résolution de la fiche.
 * 
 * @param integer ficheId ID de la fiche
 * @param boolean resolu  Statut nouveau de resolu
 */
CommunautePratique_Fiche.changeResolution = function(ficheId, resolu)
{
    $.ajax({
        url: Routing.generate((resolu ? 'hopitalnumerique_communautepratique_fiche_close' : 'hopitalnumerique_communautepratique_fiche_open'), { fiche: ficheId }),
        method: 'post',
        dataType: 'json'
    }).done(function(response) {
        if (true != response.success) {
            alert('Modification de la résolution non effectuée.');
        } else {
            window.location = Routing.generate('hopitalnumerique_communautepratique_fiche_view', { fiche: ficheId });
        }
    });
};
