<?php

namespace HopitalNumerique\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\NotificationBundle\Enum\NotificationFrequencyEnum;
use HopitalNumerique\NotificationBundle\Exception\InvalidFrequencyException;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Notification - a notification to be processed.
 *
 * @ORM\Table(name="hn_notification")
 * @ORM\Entity(repositoryClass="HopitalNumerique\NotificationBundle\Repository\NotificationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Notification
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"comment"="Notification id"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="uniqueId", type="string", length=32, options={"comment"="This id is used to deduplicate"})
     */
    protected $uniqueId;

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
     * @var User
     *
     * @ORM\JoinColumn(name="userId", referencedColumnName="usr_id", onDelete="CASCADE")
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, options={"comment"="Notification title"})
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true, options={"comment"="Notification detail"})
     */
    protected $detail;

    /**
     * @var array
     *
     * @ORM\Column(type="array", nullable=true, options={"comment"="Notification additional data"})
     */
    protected $data;

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
     * @ORM\Column(type="smallint", options={"comment"="Notification level of details (0 is lowest detail)"})
     *
     * @Assert\NotBlank()
     */
    protected $detailLevel;

    /**
     * Sending scheduled date and time.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", options={"comment"="Notification sending scheduled date time"})
     */
    protected $scheduleFor;

    /**
     * Creation date and time.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", options={"comment"="Notification creation date time"})
     */
    protected $createdAt;

    /**
     * Notification constructor.
     *
     * @param $uniqueId
     * @param $notificationCode
     * @param null $title
     * @param null $detail
     * @param array $data
     */
    public function __construct($uniqueId, $notificationCode, $title = null, $detail = null, array $data = [])
    {
        $this->uniqueId = is_array($uniqueId) ? md5(implode($uniqueId)) : $uniqueId;
        $this->notificationCode = $notificationCode;
        $this->setTitle($title);
        $this->setDetail($detail);
        $this->setData($data);
        $this->createdAt = new \DateTime();
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
     * Get unique id.
     *
     * @return int
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
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
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return Notification
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Notification
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get detail.
     *
     * @return string
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * Set detail.
     *
     * @param string $detail
     *
     * @return Notification
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     * @param array $data Notification data
     *
     * @return Notification
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return Notification
     */
    public function addData($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @param string|null $key Searched key.
     *
     * @return array|mixed
     */
    public function getData($key = null)
    {
        if (null === $key) {
            return $this->data;
        } elseif (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        } else {
            return null;
        }
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
     * @param string $notificationFrequency
     *
     * @return Notification
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
     * @return Notification
     */
    public function setDetailLevel($detailLevel)
    {
        $this->detailLevel = $detailLevel;

        return $this;
    }

    /**
     * Get notification scheduled date time.
     *
     * @return \DateTime
     */
    public function getScheduledFor()
    {
        return $this->scheduleFor;
    }

    /**
     * Set notification scheduled date time.
     *
     * @param \Datetime $scheduledDateTime
     *
     * @return Notification
     */
    public function setScheduledFor(\Datetime $scheduledDateTime)
    {
        $this->scheduleFor = $scheduledDateTime;

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
}
