<?php

namespace HopitalNumerique\NotificationBundle\Service;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\NotificationBundle\Enum\NotificationDayEnum;
use HopitalNumerique\NotificationBundle\Enum\NotificationDetailLevelEnum;
use HopitalNumerique\NotificationBundle\Enum\NotificationFrequencyEnum;
use HopitalNumerique\NotificationBundle\Event\NotificationEvent;
use HopitalNumerique\NotificationBundle\Events;
use HopitalNumerique\NotificationBundle\Model\NotificationConfigLabels;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class NotificationProviderAbstract.
 */
abstract class NotificationProviderAbstract implements NotificationProviderInterface
{
    /**
     * Default frequency of this notification provider.
     */
    const DEFAULT_FREQUENCY = NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_STRAIGHT;

    /**
     * Default detail level of this notification provider.
     */
    const DEFAULT_DETAIL_LEVEL = NotificationDetailLevelEnum::NOTIFICATION_DETAIL_LEVEL_NORMAL;

    /**
     * Default schedule day.
     */
    const DEFAULT_SCHEDULE_DAY = NotificationDayEnum::NOTIFICATION_DAY_MONDAY;

    /**
     * Default schedule hour.
     */
    const DEFAULT_SCHEDULE_HOUR = 0;

    /**
     * Used to define text size limit for notification titles.
     */
    const LIMIT_NOTIFY_TITLE_LENGTH = 30;

    /**
     * Used to define text size limit for notification descriptions.
     */
    const LIMIT_NOTIFY_DETAIL_LENGTH = 300;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var TokenStorageInterface $tokenStorage
     */
    protected $tokenStorage;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $templatePath;

    /**
     * ReportSharedForOtherNotificationProvider constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface $translator
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->templatePath = '@HopitalNumeriqueUser/Notifications/'. $this::NOTIFICATION_CODE .'.html.twig';
    }

    /**
     * @return NotificationConfigLabels
     */
    public function getConfigLabels()
    {
        return new NotificationConfigLabels($this->getSectionCode(), $this->getNotificationCode());
    }

    /**
     * Returns provider default frequency (see NotificationFrequencyEnum constants)
     *
     * @return string
     */
    public static function getDefaultFrequency()
    {
        return static::DEFAULT_FREQUENCY;
    }

    /**
     * Returns provider default detail level (see NotificationDetailLevelEnum constants)
     *
     * @return int
     */
    public static function getDefaultDetailLevel()
    {
        return static::DEFAULT_DETAIL_LEVEL;
    }

    /**
     * Returns provider default schedule day (see NotificationDayEnum constants)
     *
     * @return int
     */
    public static function getDefaultScheduleDay()
    {
        return static::DEFAULT_SCHEDULE_DAY;
    }

    /**
     * Returns provider default schedule hour (0 to 23)
     *
     * @return int
     */
    public static function getDefaultScheduleHour()
    {
        return static::DEFAULT_SCHEDULE_HOUR;
    }

    /**
     * Returns provider max title length for notification.
     *
     * @return int
     */
    public static function getLimitNotifyTitleLength()
    {
        return static::LIMIT_NOTIFY_TITLE_LENGTH;
    }

    /**
     * Returns provider max detail length for notification.
     *
     * @return int
     */
    public static function getLimitNotifyDetailLength()
    {
        return static::LIMIT_NOTIFY_DETAIL_LENGTH;
    }

    /**
     * Get subscribers list
     *
     * @param Notification $notification
     *
     * @return QueryBuilder|null
     */
    public function getSubscribers(Notification $notification)
    {
        return null;
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        if ($this->tokenStorage->getToken()) {
            return $this->tokenStorage->getToken()->getUser();
        } else {
            return null;
        }
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
        $notification = new Notification($uid, static::getNotificationCode());
        $notification->setTitle($title);
        $notification->setDetail($detail);
        $notification->setData($data);

        //Sends with notification event.
        $notificationEvent = new NotificationEvent($notification);
        $this->eventDispatcher->dispatch(Events::FIRE_NOTIFICATION, $notificationEvent);
    }

    public function getTemplatePath()
    {
        return $this->templatePath;
    }
}
