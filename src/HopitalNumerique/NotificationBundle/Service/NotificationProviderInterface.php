<?php

namespace HopitalNumerique\NotificationBundle\Service;

use Doctrine\ORM\QueryBuilder;
use \HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Model\NotificationConfigLabels;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Interface NotificationProviderInterface.
 */
interface NotificationProviderInterface
{
    /**
     * @return string
     */
    public static function getNotificationCode();

    /**
     * @return string
     */
    public static function getSectionCode();

    /**
     * @return NotificationConfigLabels
     */
    public function getConfigLabels();

    /**
     * Returns users who are concerned by notification.
     *
     * @param Notification $notification
     *
     * @return QueryBuilder|null
     */
    public function getSubscribers(Notification $notification);

    /**
     * Sends notification.
     *
     * @param Notification  $notification
     */
    public function notify(Notification $notification);
}
