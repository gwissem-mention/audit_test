<?php

namespace HopitalNumerique\ObjetBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * RefObjetRepository
 */
class RefObjetRepository extends EntityRepository
{
    /**
     * Retourne la liste des références objet pour la recherche (prend en compte les dates de publication)
     *
     * @param array $references Liste des références
     *
     * @return QueryBuilder
     */
    public function getObjetsForRecherche( $references )
    {
        $qb = $this->_em->createQueryBuilder();

        return $qb->select('refO')
                    ->from('\HopitalNumerique\ObjetBundle\Entity\RefObjet', 'refO')
                    ->leftJoin('refO.objet','obj')
                    ->andWhere('refO.reference in (:references)','obj.etat = 3')
                    ->setParameter('references', $references )
                    ->orderBy('refO.primary', 'ASC');
    }
}