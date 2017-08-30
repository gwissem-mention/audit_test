<?php

namespace HopitalNumerique\NotificationBundle\Service;

use HopitalNumerique\NotificationBundle\Event\NotificationEvent;
use HopitalNumerique\NotificationBundle\Events;
use HopitalNumerique\NotificationBundle\Model\NotificationConfigLabels;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use HopitalNumerique\NotificationBundle\Model\Notification as NotificationModel;

/**
 * Class NotificationProviderAbstract.
 */
abstract class NotificationProviderAbstract implements NotificationProviderInterface
{
    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var TokenStorageInterface $tokenStorage
     */
    protected $tokenStorage;

    /**
     * ReportSharedForOtherNotificationProvider constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface    $tokenStorage
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return NotificationConfigLabels
     */
    public function getConfigLabels()
    {
        return new NotificationConfigLabels($this->getSectionCode(), $this->getNotificationCode());
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        return $this->tokenStorage->getToken()->getUser();
    }

    /**
     * Prepare and submit notification with FIRE_NOTIFICATION event.
     *
     * @param string|array $uid    Notification unique identifier (string, or array to be stringified with md5)
     * @param string       $title  Notification title
     * @param string|null  $detail Notification detail
     * @param array        $data   Notification additional data
     */
    protected function processNotification($uid, $title, $detail = null, array $data = [])
    {
        if (!is_array($uid)) {
            $uid = [$uid];
        }
        array_unshift($uid, static::getNotificationCode());

        //Build new notification object.
        $notification = new NotificationModel($uid, static::getNotificationCode(), $title, $detail, $data);

        //Sends with notification event.
        $notificationEvent = new NotificationEvent($notification);
        $this->eventDispatcher->dispatch(Events::FIRE_NOTIFICATION, $notificationEvent);
    }
}
