<?php

namespace HopitalNumerique\ForumBundle\Model\Component\Repository;

use CCDNForum\ForumBundle\Model\Component\Repository\SubscriptionRepository as CCDNSubscriptionRepository;
use HopitalNumerique\ForumBundle\Entity\Board;
use Symfony\Component\Security\Core\User\UserInterface;
use HopitalNumerique\ForumBundle\Entity\Topic;

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
    
    /**
     * Retourne les Subscription à envoyer dès que l'on répond à un topic.
     *
     * @param \HopitalNumerique\ForumBundle\Entity\Topic $topic
     */
    public function findAllSubscriptionsToSend(Topic $topic)
    {
        $params = array(
            ':topicId' => $topic->getId(),
            ':boardId' => $topic->getBoard()->getId()
        );
        
        $qb = $this->createSelectQuery(array('s', 'b2', 't', 'b', 'c', 'f', 'fp', 'fp_author', 'lp', 'lp_author', 't_closedBy', 't_deletedBy', 't_stickiedBy'));

        $qb
            // Rechercher depuis le Topic
            ->leftJoin('s.topic', 't')
            ->innerJoin('s.forum', 'f')
            ->leftJoin('t.firstPost', 'fp')
                ->leftJoin('fp.createdBy', 'fp_author')
            ->leftJoin('t.lastPost', 'lp')
                ->leftJoin('lp.createdBy', 'lp_author')
            ->leftJoin('t.closedBy', 't_closedBy')
            ->leftJoin('t.deletedBy', 't_deletedBy')
            ->leftJoin('t.stickiedBy', 't_stickiedBy')
            ->leftJoin('t.board', 'b')
            ->leftJoin('b.category', 'c')

            // Rechercher depuis le Board
            ->leftJoin('s.board', 'b2')
            
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->eq('s.board', ':boardId'),
                    $qb->expr()->andX(
                        $qb->expr()->eq('s.topic', ':topicId'),
                        $qb->expr()->eq('t.isDeleted', 'FALSE')
                    )
                )
            )
            ->groupBy('s.ownedBy')
            ->setParameters($params)
        ;
        
        return $this->gateway->findSubscriptions($qb, $params);
    }
}
