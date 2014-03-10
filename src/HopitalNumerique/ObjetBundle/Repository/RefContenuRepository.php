<?php

namespace HopitalNumerique\ObjetBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * RefContenuRepository
 */
class RefContenuRepository extends EntityRepository
{
    /**
     * Retourne la liste des références objet pour la recherche (prend en compte les dates de publication)
     *
     * @param array $references Liste des références
     *
     * @return QueryBuilder
     */
    public function getContenusForRecherche( $references )
    {
        $qb = $this->_em->createQueryBuilder();

        return $qb->select('refC')
                  ->from('\HopitalNumerique\ObjetBundle\Entity\RefContenu', 'refC')
                  ->leftJoin('refC.contenu','con')
                  ->leftJoin('con.objet','obj')
                  ->andWhere('refC.reference in (:references)')
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
                  ->setParameter('today', new \DateTime() );
    }
}