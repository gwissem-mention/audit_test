<?php

namespace HopitalNumerique\ForumBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Subscription.
 */
class SubscriptionManager extends BaseManager
{
    protected $class = 'HopitalNumerique\ForumBundle\Entity\Subscription';

    public function deleteSubscriptionByTopicsArray($topics)
    {
        $subscriptionToDelete = [];

        foreach ($topics as $topic) {
            foreach ($topic->getSubscriptions() as $subscription) {
                $subscriptionToDelete[] = $subscription;
            }
        }

        $this->delete($subscriptionToDelete);
    }
}
