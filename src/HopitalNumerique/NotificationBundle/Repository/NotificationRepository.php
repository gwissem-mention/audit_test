<?php

namespace HopitalNumerique\NotificationBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\NotificationBundle\Entity\Notification;

/**
 * NotificationRepository
 */
class NotificationRepository extends EntityRepository
{
    /**
     * Return notifications whose scheduled date is lower or equal to $limitDate.
     *
     * @param \DateTime $limitDate
     *
     * @return Notification[]
     */
    public function getNotificationsToSend(\DateTime $limitDate)
    {
        return $this->createQueryBuilder('notification')
            ->where('notification.scheduleFor <= :limitDate')
            ->setParameter('limitDate', $limitDate)
            ->orderBy('IDENTITY(notification.user), notification.frequency')
            ->getQuery()
            ->getResult()
        ;
    }
}
