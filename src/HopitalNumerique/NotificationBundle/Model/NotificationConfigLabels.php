<?php

namespace HopitalNumerique\NotificationBundle\Model;

/**
 * Class NotificationConfigLabels.
 */
class NotificationConfigLabels
{
    /**
     * @var string $notificationSection
     */
    protected $notificationSection;

    /**
     * @var string $code Notification code
     */
    protected $notificationCode;

    /**
     * NotificationConfigLabels constructor.
     *
     * @param $notificationSection
     * @param $notificationCode
     */
    public function __construct(
        $notificationSection,
        $notificationCode
    ) {
        $this->notificationSection = $notificationSection;
        $this->notificationCode = $notificationCode;
    }

    /**
     * @return string
     */
    public function getNotificationCode()
    {
        return $this->notificationCode;
    }

    /**
     * @return string
     */
    public function getSectionLabel()
    {
        return 'notification_config.'.$this->notificationSection.'.section_title';
    }

    /**
     * @return string
     */
    public function getTitleLabel()
    {
        return $this->getTransCodeStart().'.title';
    }

    /**
     * @return string
     */
    public function getDetailLabel()
    {
        return $this->getTransCodeStart().'.detail';
    }

    /**
     * @return string
     */
    public function getDescriptionLabel()
    {
        return $this->getTransCodeStart().'.description';
    }

    /**
     * @return string
     */
    protected function getTransCodeStart()
    {
        return 'notification_config.'.$this->notificationSection.'.'.$this->notificationCode;
    }
}
