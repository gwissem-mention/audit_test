<?php

namespace HopitalNumerique\ForumBundle\Repository;

use \CCDNForum\ForumBundle\Model\Component\Repository\SubscriptionRepository as ParentSubscriptionRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * SubscriptionRepository.
 */
class SubscriptionRepository extends ParentSubscriptionRepository
{
    /**
     * Returns query builder of topic subscribers.
     *
     * @param int $topicId
     * @param int|null $boardId
     *
     * @return QueryBuilder
     */
    public function getTopicSubscribersQueryBuilder($topicId, $boardId = null)
    {
        $qb = $this->createSelectQuery(['s'])
            ->select('user.id')
            ->join('s.ownedBy', 'user')
            ->where('s.topic = :topicId')
            ->setParameter('topicId', $topicId)
        ;

        if (null !== $boardId) {
            $qb
                ->orWhere('s.board = :boardId')
                ->setParameter('boardId', $boardId)
            ;
        }

        return $qb;
    }

    /**
     * Returns query builder of board subscribers.
     *
     * @param $boardId
     *
     * @return mixed
     */
    public function getBoardSubscribersQueryBuilder($boardId)
    {
        return $this->createSelectQuery(['s'])
            ->select('user.id')
            ->join('s.ownedBy', 'user')
            ->where('s.board = :boardId')
            //Below clause is not mandatory since topicId always seems to be null when boardId is filled
            //(and vice versa), but just in case...
            ->andWhere($this->getQueryBuilder()->expr()->isNull('s.topic'))
            ->setParameter('boardId', $boardId)
        ;
    }
}
