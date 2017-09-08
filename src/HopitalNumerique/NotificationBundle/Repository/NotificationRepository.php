<?php

namespace HopitalNumerique\NotificationBundle\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;
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
     * @return IterableResult
     */
    public function getNotificationsToSend(\DateTime $limitDate)
    {
        return $this->createQueryBuilder('notification')
            ->where('notification.scheduleFor <= :limitDate')
            ->setParameter('limitDate', $limitDate)
            ->getQuery()
            ->iterate();
    }

    /**
     * Delete a notification.
     *
     * @param Notification $notification
     * @param bool $flush
     */
    public function deleteNotification(Notification $notification, $flush = true)
    {
        $this->_em->remove($notification);
        $this->_em->flush();
    }

    /**
     * Clean duplicated notifications (more than one row for same userId and same uniqueId).
     */
    public function cleanDuplicates()
    {
        foreach ($this->findLatestDateForDuplicates() as $duplicate) {
            $this->deleteDuplicate(
                $duplicate['userId'],
                $duplicate['uniqueId'],
                new \DateTime($duplicate['maxCreatedAt'])
            );
        }
    }

    /**
     * Retrieve latest notification created date for duplicated notifications.
     *
     * @return array
     */
    protected function findLatestDateForDuplicates()
    {
        return $this->createQueryBuilder('notification')
            ->select(
                'IDENTITY(notification.user) AS userId,
                notification.uniqueId,
                MAX(notification.createdAt) AS maxCreatedAt'
            )
            ->groupBy('notification.user, notification.uniqueId')
            ->having('COUNT(notification) > 1')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY)
        ;
    }

    /**
     * Delete duplicated notifications for a user and a unique id. This will limit deletion to notification created
     * before $latestCreatedDate.
     *
     * @param integer   $userId
     * @param integer   $uniqueId
     * @param \DateTime $latestCreatedDate
     */
    protected function deleteDuplicate($userId, $uniqueId, \DateTime $latestCreatedDate)
    {
        $this->createQueryBuilder('notification')
            ->delete('NotificationBundle:Notification', 'notification')
            ->where('notification.user = :user')
            ->andWhere('notification.uniqueId = :uniqueId')
            ->andWhere('notification.createdAt < :latestCreatedDate')
            ->setParameters([
                'user' => $userId,
                'uniqueId' => $uniqueId,
                'latestCreatedDate' => $latestCreatedDate
            ])
            ->getQuery()
            ->execute();
        ;
    }
}
