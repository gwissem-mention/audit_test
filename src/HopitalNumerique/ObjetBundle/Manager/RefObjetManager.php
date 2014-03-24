<?php

namespace HopitalNumerique\ObjetBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

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