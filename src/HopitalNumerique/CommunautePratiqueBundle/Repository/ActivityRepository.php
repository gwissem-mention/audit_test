<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\DomaineBundle\Entity\Domaine;

/**
 * Activity repository.
 */
class ActivityRepository extends EntityRepository
{
    /**
     * @param Domaine|null $domain
     * @param int $limit
     *
     * @return array
     */
    public function getLatest(Domaine $domain = null, $limit = 20)
    {
        $queryBuilder = $this->createQueryBuilder('activity')
            ->join('activity.objectIdentity', 'objectIdentity')->addSelect('objectIdentity')
        ;

        if ($domain && false) {
            $queryBuilder
                ->join('discussion.domains', 'domain', Join::WITH, 'domain.id = :domain')
                ->setParameter('domain', $domain)
            ;
        }

        return $queryBuilder
            ->addOrderBy('activity.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
