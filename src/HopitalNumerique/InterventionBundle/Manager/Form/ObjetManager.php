<?php
/**
 * Manager pour les objets utilisés dans les formulaires des demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager\Form;

use HopitalNumerique\UserBundle\Entity\User;

/**
 * Manager pour les objets utilisés dans les formulaires des demandes d'intervention.
 */
class ObjetManager
{
    /**
     * Retourne la liste jsonifiée des objets d'un ambassadeur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur L'ambassadeur
     * @return string La liste des objets de l'ambassadeur
     */
    public function jsonObjetsByAmbassadeur(User $ambassadeur)
    {
        $objetsListeFormatee = array();
    
        foreach ($ambassadeur->getObjets() as $objet)
        {
            $objetsListeFormatee[] = array(
                'id' => $objet->getId(),
                'titre' => $objet->getTitre()
            );
        }
    
        return json_encode($objetsListeFormatee);
    }
}
