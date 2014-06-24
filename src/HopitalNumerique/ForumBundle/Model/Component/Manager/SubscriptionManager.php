<?php

namespace HopitalNumerique\ForumBundle\Model\Component\Manager;

use CCDNForum\ForumBundle\Model\Component\Manager\SubscriptionManager as SubscriptionCCDNManager;

use HopitalNumerique\ForumBundle\Entity\Subscription;
use CCDNForum\ForumBundle\Entity\Topic;

use Symfony\Component\Security\Core\User\UserInterface;

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
}
