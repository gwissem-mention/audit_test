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
                        ->andWhere(
                            $qb->expr()->orx(
                                $qb->expr()->isNull('obj.dateDebutPublication'),
                                $qb->expr()->lte('obj.dateDebutPublication', ':today')
                            ),
                            $qb->expr()->orx(
                                $qb->expr()->isNull('obj.dateFinPublication'),
                                $qb->expr()->gte('obj.dateFinPublication', ':today')
                            )
                        )
                        ->setParameter('references', $references )
                        ->setParameter('today', new \DateTime() )
                        ->orderBy('refO.primary', 'ASC');
    }
}