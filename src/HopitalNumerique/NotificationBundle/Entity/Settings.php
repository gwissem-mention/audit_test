<?php

namespace HopitalNumerique\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\NotificationBundle\Enum\NotificationDayEnum;
use HopitalNumerique\NotificationBundle\Enum\NotificationFrequencyEnum;
use HopitalNumerique\NotificationBundle\Exception\InvalidFrequencyException;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Settings - this is where notification user settings are saved.
 *
 * @ORM\Table(name="hn_notification_user_settings")
 * @ORM\Entity(repositoryClass="HopitalNumerique\NotificationBundle\Repository\SettingsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Settings
{
    /**
     * Notification id.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"comment"="Notification settings unique id"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Notification code.
     *
     * @var string
     * @ORM\Column(type="string", length=100, options={"comment"="Notification code"})
     *
     * @Assert\NotBlank()
     */
    protected $notificationCode;

    /**
     * @var integer
     *
     * @ORM\Column(name="userId")
     * @ORM\JoinColumn(referencedColumnName="usr_id")
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     */
    protected $userId;

    /**
     * Notification frequency.
     *
     * @var string
     * @ORM\Column(type="string", length=10, options={"comment"="Notification frequency (daily,weekly,straight,off)"})
     *
     * @Assert\NotBlank()
     */
    protected $frequency;

    /**
     * Notification detail level.
     *
     * @var string
     *
     * @ORM\Column(type="smallint", options={"comment"="Notification level of details (0 is lowest detail)"})
     * @Assert\NotBlank()
     */
    protected $detailLevel;

    /**
     * Notification day (in case frequency is set to weekly)
     *
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=true, options={"comment"="Schedule day (1 to 7)"})
     */
    protected $scheduleDay;

    /**
     * Notification hour (in case frequency is set to daily or weekly)
     *
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=true, options={"comment"="Schedule time (0 to 23)"})
     */
    protected $scheduleHour;

    /**
     * Creation date and time.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", options={"comment"="Setting creation date time"})
     */
    protected $createdAt;

    /**
     * Update date and time.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true, options={"comment"="Setting update date time"})
     */
    protected $updatedAt;

    /**
     * Settings constructor.
     *
     * @param string $notificationCode
     */
    public function __construct($notificationCode = null)
    {
        $this->notificationCode = $notificationCode;
    }

    /**
     * Define creation / update date.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps()
    {
        $this->setUpdatedAt(new \DateTime());
        if ($this->getCreatedAt() == null) {
            $this->createdAt = new \DateTime();
        }
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get notification code.
     *
     * @return string
     */
    public function getNotificationCode()
    {
        return $this->notificationCode;
    }

    /**
     * Get user id.
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set user id.
     *
     * @param integer $userId
     *
     * @return Settings
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get notification frequency.
     *
     * @return string
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * Set notification frequency.
     *
     * @param $notificationFrequency
     *
     * @return $this
     * @throws InvalidFrequencyException
     */
    public function setFrequency($notificationFrequency)
    {
        if (!in_array($notificationFrequency, NotificationFrequencyEnum::getFrequencies())) {
            throw new InvalidFrequencyException(sprintf(
                "frequency '%s' is not valid (one of %s expected)",
                $notificationFrequency,
                implode(',', NotificationFrequencyEnum::getFrequencies())
            ));
        }

        $this->frequency = $notificationFrequency;

        return $this;
    }

    /**
     * Get notification level of detail.
     *
     * @return integer
     */
    public function getDetailLevel()
    {
        return $this->detailLevel;
    }

    /**
     * Set notification level of detail.
     *
     * @param integer $detailLevel
     *
     * @return Settings
     */
    public function setDetailLevel($detailLevel)
    {
        $this->detailLevel = $detailLevel;

        return $this;
    }

    /**
     * Get creation datetime.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get update datetime.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set update datetime.
     *
     * @param \DateTime $updatedAt
     *
     * @return Settings
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get schedule day.
     *
     * @return integer
     */
    public function getScheduleDay()
    {
        return $this->scheduleDay;
    }

    /**
     * Set schedule day.
     *
     * @param integer $scheduleDay
     *
     * @return Settings
     */
    public function setScheduleDay($scheduleDay)
    {
        if (null !== $scheduleDay) {
            $scheduleDay = (int)$scheduleDay;
            if (!in_array($scheduleDay, NotificationDayEnum::getNotificationDays())) {
                throw new \InvalidArgumentException(
                    sprintf(
                        '$scheduleDay must be one of the following : %s',
                        implode(', ', NotificationDayEnum::getNotificationDays())
                    )
                );
            }
        }
        $this->scheduleDay = $scheduleDay;

        return $this;
    }

    /**
     * Get schedule hour.
     *
     * @return integer
     */
    public function getScheduleHour()
    {
        return $this->scheduleHour;
    }

    /**
     * Set schedule hour.
     *
     * @param integer $scheduleHour
     *
     * @return Settings
     */
    public function setScheduleHour($scheduleHour)
    {
        if (null !== $scheduleHour) {
            $scheduleHour = (int)$scheduleHour;
            if ($scheduleHour < 0 || $scheduleHour > 23) {
                throw new \InvalidArgumentException(
                    '$scheduleHour must be an integer between 0 (midnight) and 23 (11PM).'
                );
            }
        }
        $this->scheduleHour = $scheduleHour;

        return $this;
    }
}
