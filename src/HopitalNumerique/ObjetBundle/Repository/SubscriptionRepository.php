<?php

namespace HopitalNumerique\ObjetBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * SubscriptionRepository.
 */
class SubscriptionRepository extends EntityRepository
{
    /**
     * Returns subscribers ids for an object and optionnaly an infradoc.
     * This will filter on users whose object / infradoc last view date is lower than $maxViewDate.
     *
     * @param \DateTime    $maxViewDate
     * @param integer      $objectId
     * @param integer|null $infradocId
     *
     * @return QueryBuilder Users
     */
    public function getSubscribersQueryBuilder(\DateTime $maxViewDate, $objectId, $infradocId = null)
    {
        $queryBuilder = $this->createQueryBuilder('subscription')
            ->select('user.id')
            ->join('subscription.user', 'user')
            ->where('subscription.objet = :object')
            ->setParameter('object', (int)$objectId)
            ->innerJoin(
                'HopitalNumeriqueObjetBundle:Consultation',
                'clt',
                Join::WITH,
                'clt.objet = subscription.objet AND clt.user = user.id' .
                ($infradocId ? ' AND clt.contenu = subscription.contenu' : '')
            )
            ->andWhere('clt.dateLastConsulted < :maxViewDate')
            ->setParameter('maxViewDate', $maxViewDate)
            ->groupBy('user.id')
        ;
        if ($infradocId) {
            $queryBuilder
                ->andWhere('subscription.contenu = :infradoc')
                ->setParameter('infradoc', (int)$infradocId)
            ;
        } else {
            $queryBuilder->andWhere('subscription.contenu IS NULL');
        }

        return $queryBuilder;
    }
}
