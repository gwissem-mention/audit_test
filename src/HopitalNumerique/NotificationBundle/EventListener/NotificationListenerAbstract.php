<?php

namespace HopitalNumerique\NotificationBundle\EventListener;

use HopitalNumerique\NotificationBundle\Service\Notifications;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class NotificationListenerAbstract.
 */
abstract class NotificationListenerAbstract implements EventSubscriberInterface
{
    /**
     * @var Notifications $notificationService
     */
    protected $notificationService;

    /**
     * NotificationListenerAbstract constructor.
     *
     * @param Notifications $notificationService
     */
    public function __construct(Notifications $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Return notification provider.
     *
     * @return NotificationProviderAbstract|null
     */
    public function getProvider()
    {
        return $this->notificationService->getProvider($this->getProviderCode());
    }

    abstract protected function getProviderCode();
}
