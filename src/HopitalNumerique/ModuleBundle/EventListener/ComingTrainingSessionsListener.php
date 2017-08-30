<?php

namespace HopitalNumerique\ModuleBundle\EventListener;

use HopitalNumerique\ModuleBundle\Event\ComingTrainingSessionsEvent;
use HopitalNumerique\ModuleBundle\Events;
use HopitalNumerique\ModuleBundle\Service\Notification\ComingTrainingSessionsNotificationProvider;
use HopitalNumerique\NotificationBundle\EventListener\NotificationListenerAbstract;

/**
 * Class ComingTrainingSessionsListener.
 *
 * @method ComingTrainingSessionsNotificationProvider getProvider()
 */
class ComingTrainingSessionsListener extends NotificationListenerAbstract
{
    /**
     * @param ComingTrainingSessionsEvent $event
     */
    public function onComingTrainingSessions(ComingTrainingSessionsEvent $event)
    {
        $this->getProvider()->fire($event->getSession());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::COMING_TRAINING_SESSION => 'onComingTrainingSessions',
        ];
    }

    /**
     * @return string
     */
    protected function getProviderCode()
    {
        return ComingTrainingSessionsNotificationProvider::getNotificationCode();
    }
}
