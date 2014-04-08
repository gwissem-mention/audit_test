<?php

namespace HopitalNumerique\ObjetBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité RefObjet.
 */
class RefObjetManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ObjetBundle\Entity\RefObjet';

    /**
     * Retourne la liste des références objet pour la recherche
     *
     * @param array $references Liste des références
     *
     * @return array
     */
    public function getObjetsForRecherche( $references )
    {
        return $this->getRepository()->getObjetsForRecherche( $references )->getQuery()->getResult();
    }
}