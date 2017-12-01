<?php

namespace HopitalNumerique\NotificationBundle;

/**
 * Class Events.
 * Contains all events related to the notification bundle.
 */
final class Events
{
    /**
     * This event occurs when a notification must be saved.
     */
    const FIRE_NOTIFICATION  = 'fire_notification';

    /**
     * This event occurs when a notification must be sent.
     */
    const SEND_NOTIFICATION  = 'send_notification';

    /**
     * This event occurs when a bunch notifications must be sent.
     */
    const SEND_NOTIFICATION_GROUP  = 'send_notification_group';
}
