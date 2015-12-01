/**
 * Classe gérant les commentaires de la communauté de pratique.
 */
var CommunautePratique_Commentaire = function() {};

/**
 * Permet d'éditer le commentaire.
 *
 * @param integer commentaireId ID du commentaire
 */
CommunautePratique_Commentaire.edit = function(commentaireId)
{
    $.ajax({
        url: Routing.generate('hopitalnumerique_communautepratique_commentaire_edit', { commentaire:commentaireId }),
        method: 'post',
        success: function(html) {
            $('[data-communaute-pratique-commentaire-id=' + commentaireId + '] .message').html(html);
        }
    });
};

/**
 * Supprime le commentaire.
 *
 * @param integer commentaireId ID du commentaire
 */
CommunautePratique_Commentaire.delete = function(commentaireId)
{
    if (confirm('Confirmez-vous la suppression définitive de ce commentaire ?'))
    {
        $.ajax({
            url: Routing.generate('hopitalnumerique_communautepratique_commentaire_delete', { commentaire:commentaireId }),
            method: 'post',
            dataType: 'json'
        }).done(function(data) {
            if (data.success) {
                $('[data-communaute-pratique-commentaire-id=' + commentaireId + ']').slideUp();
            } else {
                alert('Le commentaire n\'a pu être supprimé.');
            }
        });
    }
};
