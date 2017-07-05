<?php

namespace HopitalNumerique\RechercheParcoursBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;

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
     * @param User              $owner
     * @param RechercheParcours $guidedSearchParent
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

    /**
     * Returns the user's guided searches and those that have been shared with him.
     *
     * @param User $user
     *
     * @return GuidedSearch[]
     */
    public function findByUserWithShares(User $user)
    {
        return $this->createQueryBuilder('guidedSearch')
            ->leftJoin('guidedSearch.owner', 'owner')
            ->leftJoin('guidedSearch.shares', 'shares')
            ->where('owner.id = :userId')
            ->orWhere('shares.id = :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('guidedSearch.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
