<?php

namespace HopitalNumerique\NotificationBundle\EventListener;

use HopitalNumerique\NotificationBundle\Service\Notification;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class NotificationListenerAbstract.
 */
abstract class NotificationListenerAbstract implements EventSubscriberInterface
{
    protected $notificationAggregator;

    /**
     * NotificationListenerAbstract constructor.
     *
     * @param Notification $notificationAggregator
     */
    public function __construct(Notification $notificationAggregator)
    {
        $this->notificationAggregator = $notificationAggregator;
    }

    /**
     * Return notification provider.
     *
     * @return NotificationProviderAbstract|null
     */
    public function getProvider()
    {
        return $this->notificationAggregator->getProvider($this->getProviderCode());
    }

    abstract protected function getProviderCode();
}
