<?php

namespace HopitalNumerique\RechercheParcoursBundle\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
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
     * @param Domaine[] $domains
     *
     * @return GuidedSearch[]
     */
    public function findByUserWithShares(User $user, $domains = [])
    {
        $queryBuilder = $this->createQueryBuilder('guidedSearch')
            ->leftJoin('guidedSearch.owner', 'owner')
            ->leftJoin('guidedSearch.shares', 'shares')
            ->where('owner.id = :userId')
            ->orWhere('shares.id = :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('guidedSearch.createdAt', 'DESC')
        ;

        if (count($domains) > 0) {
            $queryBuilder
                ->join('guidedSearch.guidedSearchReference', 'guidedSearchReference')
                ->join('guidedSearchReference.recherchesParcoursGestion', 'guidedSearchConfig')
                ->join('guidedSearchConfig.domaines', 'domain', Join::WITH, $queryBuilder->expr()->in('domain.id', array_map(function (Domaine $domain) {
                    return $domain->getId();
                }, $domains)))
            ;
        }

        return $queryBuilder
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Updates guided search update date.
     *
     * @param GuidedSearch $guidedSearch
     *
     * @return GuidedSearchRepository
     */
    public function touch(GuidedSearch $guidedSearch)
    {
        $guidedSearch->setUpdatedAt();
        $this->_em->persist($guidedSearch);
        $this->_em->flush($guidedSearch);

        return $this;
    }

    /**
     * Retrieves the users whose last guided search ($parcoursGestionId) update was before $maxUpdateDate.
     *
     * @param integer   $parcoursGestionId
     * @param \DateTime $maxUpdateDate
     *
     * @return QueryBuilder Users
     */
    public function getUpdatersBeforeQueryBuilder($parcoursGestionId, \DateTime $maxUpdateDate)
    {
        return $this->createQueryBuilder('guidedSearch')
            ->select('user.id')
            ->innerJoin(
                RechercheParcours::class,
                'rech_parcours',
                Join::WITH,
                'rech_parcours.id = guidedSearch.guidedSearchReference'
            )
            ->innerJoin(
                RechercheParcoursGestion::class,
                'rech_parcours_gest',
                Join::WITH,
                'rech_parcours.recherchesParcoursGestion = rech_parcours_gest.id AND rech_parcours_gest.id = :parcGest'
            )
            ->innerJoin('guidedSearch.owner', 'user')
            ->groupBy('user.id')
            ->having('MAX(guidedSearch.updatedAt) < :maxUpdateDate')
            ->setParameters([
                'parcGest' => (int)$parcoursGestionId,
                'maxUpdateDate' => $maxUpdateDate
            ])
        ;
    }
}
