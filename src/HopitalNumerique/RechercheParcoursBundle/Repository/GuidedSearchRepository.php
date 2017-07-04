<?php

namespace HopitalNumerique\RechercheParcoursBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * GuidedSearchRepository.
 */
class GuidedSearchRepository extends EntityRepository
{
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function createFindFullyQueryBuilder()
    {
        return $this->createQueryBuilder('gs')
            ->leftJoin('gs.privateRisks', 'pr')->addSelect('pr')
            ->leftJoin('gs.owner', 'o')->addSelect('o')
            ->leftJoin('gs.guidedSearchReference', 'gsr')->addSelect('gsr')
        ;
    }

    /**
     * @param User $owner
     *
     * @return GuidedSearch|null
     */
    public function findLatestByOwnerAndGuidedSearchReference(User $owner, RechercheParcours $guidedSearchParent)
    {
        return $this
            ->createFindFullyQueryBuilder()

            ->andWhere('o.id = :ownerId')->setParameter('ownerId', $owner->getId())
            ->andWhere('gsr.id = :guidedSearchParentId')->setParameter('guidedSearchParentId', $guidedSearchParent->getId())

            ->orderBy('gs.createdAt', 'DESC')

            ->setMaxResults(1)

            ->getQuery()->getOneOrNullResult()
        ;
    }
}
