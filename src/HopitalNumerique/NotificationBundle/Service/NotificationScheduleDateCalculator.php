<?php

namespace HopitalNumerique\NotificationBundle\Service;

use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Entity\Settings;
use HopitalNumerique\NotificationBundle\Enum\NotificationDayEnum;
use HopitalNumerique\NotificationBundle\Enum\NotificationFrequencyEnum;
use HopitalNumerique\NotificationBundle\Exception\InvalidFrequencyException;

/**
 * Class NotificationScheduleDateCalculator.
 * This class is used to find a notification scheduled date depending on schedule settings and current date time.
 */
class NotificationScheduleDateCalculator
{
    /**
     * @var array $dayNames
     */
    protected $dayNames;

    /**
     * @var \DateTime $now
     */
    protected $now;

    /**
     * @var int $currentDay
     */
    protected $currentDay;

    /**
     * @var int $currentHour
     */
    protected $currentHour;

    /**
     * NotificationScheduleDateCalculator constructor.
     */
    public function __construct()
    {
        $this->dayNames = NotificationDayEnum::getDayNames();
        $this->initNow();
    }

    /**
     * Initialize current date time.
     */
    public function initNow()
    {
        $this->now = new \DateTime();
        $this->currentDay = (int)$this->now->format('N');
        $this->currentHour = (int)$this->now->format('H');
    }

    /**
     * @param Notification $notification
     * @param Settings $subscription
     *
     * @return \DateTime
     * @throws InvalidFrequencyException
     */
    public function calculateScheduleDateTime(Notification $notification, Settings $subscription)
    {
        switch ($subscription->getFrequency()) {
            case NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_STRAIGHT:
                $scheduleDateTime = $notification->getCreatedAt();
                break;

            case NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_WEEKLY:
                //If notification is scheduled for today and schedule hour is not past
                if ($subscription->getScheduleDay() === $this->currentDay &&
                    $subscription->getScheduleHour() > $this->currentHour) {
                    $nextDay = '';
                } else {
                    $nextDay = 'Next ';
                }
                $scheduleDateTime = new \DateTime(sprintf(
                    $nextDay.'%s %d:00',
                    $this->dayNames[$subscription->getScheduleDay()],
                    $subscription->getScheduleHour()
                ));
                break;

            case NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_DAILY:
                //If notification schedule hour is not past, send it today, otherwise tomorrow.
                if ($subscription->getScheduleHour() > $this->currentHour) {
                    $scheduleDateTime = new \DateTime('now');
                } else {
                    $scheduleDateTime = new \DateTime('tomorrow');
                }
                $scheduleDateTime->setTime($subscription->getScheduleHour(), 0);
                break;

            default:
                throw new InvalidFrequencyException(sprintf(
                    "frequency '%s' is not valid (one of %s expected)",
                    $subscription->getFrequency(),
                    implode(',', NotificationFrequencyEnum::getFrequencies())
                ));

        }

        return $scheduleDateTime;
    }
}
