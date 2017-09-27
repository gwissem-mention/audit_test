<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion;

use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Query\Discussion\DiscussionDisplayQuery;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Query\Discussion\DiscussionListQuery;

/**
 * Class DiscussionRepository
 */
class DiscussionRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param DiscussionListQuery $query
     *
     * @return Discussion[]
     */
    public function queryForDiscussionList(DiscussionListQuery $query)
    {
        $queryBuilder = $this->createQueryBuilder('discussion')

            ->select('discussion')
            ->addSelect('message')
            ->addSelect('messageUser')
            ->addSelect('cdpGroup')

            ->join('discussion.domains', 'domain', Join::WITH, 'domain IN (:domains)')
            ->setParameter('domains', $query->domains)
            ->join('discussion.messages', 'message')
            ->join('message.user', 'messageUser')
            ->leftJoin('discussion.groups', 'cdpGroup')
        ;

        if (null !== $query->user) {
            $queryBuilder
                ->leftJoin('discussion.readings', 'reading', Join::WITH, 'reading.user = :user')
                ->leftJoin('reading.user', 'reader')
                ->addSelect('reading', 'reader')
                ->orWhere('message.user = :user')
                ->setParameter('user', $query->user)
            ;
        }

        if (!$query->displayAll) {
            $queryBuilder->orWhere('message.published = TRUE');

            if (count($query->displayAllForGroups)) {
                $queryBuilder
                    ->orWhere('cdpGroup IN (:displayAllForGroups)')
                    ->setParameter('displayAllForGroups', $query->displayAllForGroups)
                ;
            }
        }


        $queryBuilder
            ->addOrderBy('message.createdAt', 'DESC')
            ->addGroupBy('message.id')
            ->having('COUNT(message) > 0')
        ;

        return $queryBuilder
            ->getQuery()->getResult()
        ;
    }

    /**
     * @param DiscussionDisplayQuery $query
     *
     * @return Discussion
     */
    public function queryForDiscussionDisplayQuery(DiscussionDisplayQuery $query)
    {
        $queryBuilder = $this
            ->createQueryBuilder('discussion')
            ->addSelect('message')
            ->addSelect('author')
            ->addSelect('cdpGroup')
            ->addSelect('animators')

            ->join('discussion.messages', 'message')
            ->join('message.user', 'author')
            ->leftJoin('discussion.groups', 'cdpGroup')
            ->leftJoin('cdpGroup.animateurs', 'animators')
            ->join('discussion.domains', 'domain', Join::WITH, 'domain IN (:domains)')
            ->setParameter('domains', $query->domains)

            ->andWhere('discussion = :discussion')
            ->setParameter('discussion', $query->discussion)
        ;

        if (null !== $query->user) {
            $queryBuilder
                ->leftJoin('discussion.readings', 'reading', Join::WITH, 'reading.user = :user')
                ->leftJoin('reading.user', 'reader')
                ->addSelect('reading', 'reader')
                ->setParameter('user', $query->user)
            ;
        }

        if (!$query->displayAll) {
            if (null !== $query->user) {
                $queryBuilder
                    ->andWhere('message.published = TRUE OR message.user = :user')
                    ->setParameter('user', $query->user)
                ;
            } else {
                $queryBuilder->andWhere('message.published = TRUE');
            }
        }

        $queryBuilder
            ->addOrderBy('message.createdAt', 'ASC')
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
