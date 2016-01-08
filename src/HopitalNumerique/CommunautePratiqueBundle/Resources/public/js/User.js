/**
 * Classe gérant les utilisateurs de la communauté de pratique.
 */
var CommunautePratique_User = function() {};

/**
 * Supprime un utilisateur.
 * 
 * @param integer groupeId ID du groupe
 * @param integer userId   ID de l'utilisateur
 */
CommunautePratique_User.desinscritGroupe = function(groupeId, userId)
{
    if (confirm('Confirmez-vous la désinscription de ce membre ?'))
    {
        $.ajax({
            url: Routing.generate('hopitalnumerique_communautepratique_user_desinscritgroupe', { groupe: groupeId, user: userId }),
            method: 'post',
            dataType: 'json'
        }).done(function(data) {
            if (data.success) {
                window.location = Routing.generate('hopitalnumerique_communautepratique_user_listbygroupe', { groupe: groupeId });
            } else {
                alert('L\'utilisateur n\'a pu être désinscrit.');
            }
        });
    }
};
