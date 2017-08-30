<?php

namespace HopitalNumerique\ObjetBundle\Service\Notification;

use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class PublicationNotifiedNotificationProvider.
 */
class PublicationNotifiedNotificationProvider extends PublicationNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'publication_notified';

    /**
     * @return string
     */
    public static function getNotificationCode()
    {
        return self::NOTIFICATION_CODE;
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
            $title = substr($title, 0, self::getLimitNotifyTitleLength()) . '...';
        }
        
        $this->processNotification(
            $uid,
            $title,
            $reason,
            [
                'idPublication' => $object->getId(),
                'idInfradoc' => $infradoc ? $infradoc->getId() : null,
                'titrePublication' => $infradoc ? $infradoc->getTitre() : $object->getTitre(),
                'miseAJour' => $infradoc ? $infradoc->getContenu() : $object->getResume()
            ]
        );
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {

    }
}
