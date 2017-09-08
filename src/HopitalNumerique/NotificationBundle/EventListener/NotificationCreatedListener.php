<?php

namespace HopitalNumerique\NotificationBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use HopitalNumerique\NotificationBundle\Entity\Notification;

/**
 * Class NotificationCreatedListener.
 * Used to avoid notifications duplicates (deduplicate key is user + uniqueId).
 */
class NotificationCreatedListener implements EventSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return ['prePersist'];
    }

    /**
     * Remove previous similar notifications to avoid duplicates.
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Notification) {
            /** @var Notification $entity */
            $args
                ->getEntityManager()
                ->createQueryBuilder()
                ->delete('NotificationBundle:Notification', 'notification')
                ->where('notification.user = :user')
                ->andWhere('notification.uniqueId = :uid')
                ->setParameters([
                    'user' => $entity->getUser(),
                    'uid' => $entity->getUniqueId(),
                ])
                ->getQuery()
                ->execute();
        }
    }
}
