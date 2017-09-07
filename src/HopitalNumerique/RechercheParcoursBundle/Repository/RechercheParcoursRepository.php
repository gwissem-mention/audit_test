<?php

namespace HopitalNumerique\RechercheParcoursBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;

/**
 * RechercheParcoursRepository.
 */
class RechercheParcoursRepository extends EntityRepository
{
    /**
     * @param RechercheParcoursGestion $guidedSearchConfig
     *
     * @return array
     */
    public function findByGuidedSearchConfig(RechercheParcoursGestion $guidedSearchConfig)
    {
        return $this->createQueryBuilder('rp')
            ->join('rp.recherchesParcoursGestion', 'gsc', Join::WITH, 'gsc.id = :guidedSearchConfigId')
            ->join('rp.reference', 'r')->addSelect('r')
            ->setParameter('guidedSearchConfigId', $guidedSearchConfig->getId())

            ->addOrderBy('rp.order', 'ASC')

            ->getQuery()->getResult()
        ;
    }
}
