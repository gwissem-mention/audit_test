<?php

namespace HopitalNumerique\ForumBundle\Model\Component\Manager;

use CCDNForum\ForumBundle\Model\Component\Manager\SubscriptionManager as SubscriptionCCDNManager;

use HopitalNumerique\ForumBundle\Entity\Subscription;
use CCDNForum\ForumBundle\Entity\Topic;

use Symfony\Component\Security\Core\User\UserInterface;
use HopitalNumerique\ForumBundle\Entity\Board;

/**
 *
 */
class SubscriptionManager extends SubscriptionCCDNManager
{
    /**
     *
     * @access public
     * @param  int                                                 $topicId
     * @param  \Symfony\Component\Security\Core\User\UserInterface $userId
     * @return \CCDNForum\ForumBundle\Manager\ManagerInterface
     */
    public function subscribe(Topic $topic, UserInterface $user)
    {
        $subscription = $this->model->findOneSubscriptionForTopicByIdAndUserById($topic->getId(), $user->getId());

        if (! $subscription) {
            $subscription = new Subscription();
        }

        if (! $subscription->isSubscribed()) {
            $subscription->setSubscribed(true);
            $subscription->setOwnedBy($user);
            $subscription->setTopic($topic);
            $subscription->setRead(true);
            $subscription->setForum($topic->getBoard()->getCategory()->getForum());

            $this->gateway->saveSubscription($subscription);
        }

        return $this;
    }

    /**
     *
     * @access public
     * @param  \CCDNForum\ForumBundle\Entity\Board                 $board
     * @param  \Symfony\Component\Security\Core\User\UserInterface $userId
     * @return \CCDNForum\ForumBundle\Manager\ManagerInterface
     */
    public function subscribeBoard(Board $board, UserInterface $user)
    {
        $subscription = $this->model->findOneSubscriptionForBoardAndUser($board, $user);
    
        if (! $subscription) {
            $subscription = new Subscription();
        }
    
        if (! $subscription->isSubscribed()) {
            $subscription->setSubscribed(true);
            $subscription->setOwnedBy($user);
            $subscription->setBoard($board);
            $subscription->setRead(true);
            $subscription->setForum($board->getCategory()->getForum());
    
            $this->gateway->saveSubscription($subscription);
        }
    
        return $this;
    }
    
    /**
     *
     * @access public
     * @param  \CCDNForum\ForumBundle\Entity\Board             $board
     * @param  int                                             $userId
     * @return \CCDNForum\ForumBundle\Manager\ManagerInterface
     */
    public function unsubscribeBoard(Board $board, UserInterface $user)
    {
        $subscription = $this->model->findOneSubscriptionForBoardAndUser($board, $user);
    
        if (! $subscription) {
            return $this;
        }
    
        $subscription->setSubscribed(false);
        $subscription->setRead(true);
    
        $this->gateway->saveSubscription($subscription);
    
        return $this;
    }
}
