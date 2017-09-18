<?php

namespace HopitalNumerique\NotificationBundle\Domain\Command;

use HopitalNumerique\NotificationBundle\Entity\Settings;

/**
 * Class UpdateNotificationSettingsCommand.
 */
class UpdateNotificationSettingsCommand
{
    /**
     * @var Settings
     */
    public $settings;

    /**
     * UpdateNotificationSettingsCommand constructor.
     *
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

}
