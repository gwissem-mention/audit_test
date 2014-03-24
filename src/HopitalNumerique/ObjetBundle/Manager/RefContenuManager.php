<?php

namespace HopitalNumerique\ObjetBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Manager de l'entité RefContenu.
 */
class RefContenuManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ObjetBundle\Entity\RefContenu';

    /**
     * Retourne la liste des références contenu pour la recherche
     *
     * @param array $references Liste des références
     *
     * @return array
     */
    public function getContenusForRecherche( $references )
    {
        return $this->getRepository()->getContenusForRecherche( $references )->getQuery()->getResult();
    }
}