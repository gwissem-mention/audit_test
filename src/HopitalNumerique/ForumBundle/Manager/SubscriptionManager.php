<?php

namespace HopitalNumerique\ForumBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Subscription.
 */
class SubscriptionManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ForumBundle\Entity\Subscription';

    public function deleteSubscriptionByTopicsArray($topics)
    {   
        $subscriptionToDelete = array();

        foreach ($topics as $topic) 
        {
            foreach ($topic->getSubscriptions() as $subscription) 
            {
                $subscriptionToDelete[] = $subscription;
            }
        }

        $this->delete($subscriptionToDelete);
    }
}