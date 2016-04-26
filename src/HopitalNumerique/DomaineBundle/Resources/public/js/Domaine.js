/**
 * Gestion des domaines.
 */
var Hn_DomaineBundle_Domaine = new function() {};


/**
 * @var integer ID du domaine courant
 */
Hn_DomaineBundle_Domaine.CURRENT_DOMAINE_ID = null;

/**
 * @var array Liste des domaines par ID
 */
Hn_DomaineBundle_Domaine.DOMAINES_BY_ID = {};


/**
 * Initialise la liste des domaines.
 *
 * @param array domaines Domaines
 */
Hn_DomaineBundle_Domaine.setDomaines = function(domaines)
{
    for (var i in domaines) {
        Hn_DomaineBundle_Domaine.DOMAINES_BY_ID[domaines[i].id] = domaines[i];
    }
};

/**
 * Retourne le nom d'un domaine selon son ID.
 *
 * @param integer domaineId ID du domaine
 */
Hn_DomaineBundle_Domaine.getNomById = function(domaineId)
{
    return Hn_DomaineBundle_Domaine.DOMAINES_BY_ID[domaineId].nom;
};
