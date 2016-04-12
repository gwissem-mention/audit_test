/**
 * Gestion de RechercheParcours.
 */
var Hn_RechercheParcoursBundle_RechercheParcours = function() {};

/**
 * Enregistre le RechercheParcours.
 *
 * @param integer rechercheParcoursId ID du RechercheParcours
 */
Hn_RechercheParcoursBundle_RechercheParcours.save = function(rechercheParcoursId)
{
    $.ajax({
        url: Routing.generate('hopital_numerique_recherche_parcours_save', { rechercheParcours:rechercheParcoursId }),
        data: {
            description: $('#description_detail').val()
        },
        type: 'POST',
        dataType : 'json',
        success : function() {
            $.fancybox.close(true);
        }
    });
};
