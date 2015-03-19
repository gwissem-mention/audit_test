<?php

namespace HopitalNumerique\ForumBundle\Model\Component\Repository;

use CCDNForum\ForumBundle\Model\Component\Repository\SubscriptionRepository as CCDNSubscriptionRepository;
use HopitalNumerique\ForumBundle\Entity\Board;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 *
 * @category CCDNForum
 * @package  ForumBundle
 */
class SubscriptionRepository extends CCDNSubscriptionRepository
{
    /**
     *
     * @access public
     * @param  int $topicId
     * @return int
     */
    public function countSubscriptionsForTopicById($topicId)
    {
        if (null == $topicId || ! is_numeric($topicId) || $topicId == 0) {
            throw new \Exception('Topic id "' . $topicId . '" is invalid!');
        }

        $qb = $this->createCountQuery();

        $qb->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('s.topic', ':topicId'),
                    $qb->expr()->eq('s.isSubscribed', true)
                )
            );

        return $this->gateway->countSubscriptions($qb, array(':topicId' => $topicId));
    }
    
    /**
     *
     * @access public
     * @param  \HopitalNumerique\ForumBundle\Entity\Board          $board
     * @param  \Symfony\Component\Security\Core\User\UserInterface $userId
     * @return \CCDNForum\ForumBundle\Entity\Subscription
     */
    public function findOneSubscriptionForBoardAndUser(Board $board, UserInterface $user)
    {
        $qb = $this->createSelectQuery(array('s', 'board', 'user'));

        $qb
            ->leftJoin('s.board', 'board')
            ->leftJoin('s.ownedBy', 'user')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('s.board', ':boardId'),
                    $qb->expr()->eq('s.ownedBy', ':userId')
                )
            )
        ;

        return $this->gateway->findSubscription($qb, array(':boardId' => $board->getId(), ':userId' => $user->getId()));
    }
}
