<?php

namespace HopitalNumerique\ObjetBundle\Service\Notification;

use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;

/**
 * Class PublicationNotifiedNotificationProvider.
 */
class PublicationNotifiedNotificationProvider extends PublicationNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'publication_notified';

    const NOTIFICATION_MAIL = 29;

    /**
     * @return string
     */
    public static function getNotificationCode()
    {
        return self::NOTIFICATION_CODE;
    }

    /**
     * @return integer
     */
    public static function getNotifPosition()
    {
        return 1;
    }

    /**
     * Submits notification to Notification manager service via FIRE_NOTIFICATION event.
     *
     * @param Objet   $object
     * @param Contenu $infradoc
     * @param string  $reason
     */
    public function fire(Objet $object, Contenu $infradoc = null, $reason = '')
    {
        if (!$infradoc) {
            $uid = $object->getId();
            $title = $object->getTitre();
        } else {
            $uid = [$object->getId(), $infradoc];
            $title = $infradoc->getTitre();
        }

        if (strlen($title) > self::getLimitNotifyTitleLength()) {
            $title = substr(strip_tags($title), 0, self::getLimitNotifyTitleLength()) . '...';
        }
        if (strlen($reason) > self::getLimitNotifyDetailLength()) {
            $reason = substr($reason, 0, self::getLimitNotifyDetailLength()) . '...';
        }

        $this->processNotification(
            $uid,
            $title,
            $reason,
            parent::generateOptions($object, $infradoc)
        );
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
        $notification->addData('miseAJour', $notification->getDetail());
        $this->mailManager->sendPublicationNotifiedNotification($notification->getUser(), $notification->getData());
    }
}
