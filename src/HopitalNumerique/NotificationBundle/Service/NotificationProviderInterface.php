<?php

namespace HopitalNumerique\NotificationBundle\Service;

use HopitalNumerique\NotificationBundle\Model\Notification as NotificationModel;
use HopitalNumerique\NotificationBundle\Model\NotificationConfigLabels;
use Symfony\Component\EventDispatcher\Event;
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
     * Checks if a notification should be stacked for user.
     *
     * @param UserInterface     $user
     * @param NotificationModel $notification
     *
     * @return bool
     */
    public function canNotify(UserInterface $user, NotificationModel $notification);

    /**
     * Sends notification.
     *
     * @param UserInterface     $user
     * @param NotificationModel $notification
     */
    public function notify(UserInterface $user, NotificationModel $notification);
}
