<?php

namespace HopitalNumerique\ObjetBundle\Service\Notification;

use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Enum\NotificationLimitTextLengthEnum;
use HopitalNumerique\ObjetBundle\Entity\Commentaire;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class PublicationCommentedNotificationProvider.
 */
class PublicationCommentedNotificationProvider extends PublicationNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'publication_commented';

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
     * @param Commentaire $comment
     * @param Objet $object
     * @param Contenu|null $infradoc
     */
    public function fire(Commentaire $comment, Objet $object, Contenu $infradoc = null)
    {
        if (!$infradoc) {
            $uid = $object->getId();
            $title = $object->getTitre();
        } else {
            $uid = [$object->getId(), $infradoc->getId()];
            $title = $infradoc->getFullTitle();
        }

        if (strlen($title) > self::getLimitNotifyTitleLength()) {
            $title = substr($title, 0, self::getLimitNotifyTitleLength()) . '...';
        }

        $detail = $comment->getContenu();
        if (strlen($detail) > self::getLimitNotifyDetailLength()) {
            $detail = substr($detail, 0, self::getLimitNotifyDetailLength()) . '...';
        }

        $this->processNotification(
            $uid,
            $title,
            $detail,
            [
                'idPublication'    => $object->getId(),
                'idInfradoc'       => $infradoc ? $infradoc->getId() : null,
                'titrePublication' => $infradoc ? $infradoc->getTitre() : $object->getTitre(),
                'commentaire'      => $this->processText($comment->getContenu())
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
