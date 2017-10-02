<?php

namespace HopitalNumerique\ObjetBundle\Service\Notification;

use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Enum\NotificationLimitTextLengthEnum;
use HopitalNumerique\ObjetBundle\Entity\Commentaire;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Nodevo\MailBundle\Service\Traits\MailManagerAwareTrait;

/**
 * Class PublicationCommentedNotificationProvider.
 */
class PublicationCommentedNotificationProvider extends PublicationNotificationProviderAbstract
{
    use MailManagerAwareTrait;

    const NOTIFICATION_CODE = 'publication_commented';

    const NOTIFICATION_MAIL = 69;

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


        $this->processNotification(
            $uid,
            $title,
            $this->processText($comment->getTexte()),
            parent::generateOptions($object, $infradoc)
        );
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
        $notification->addData('commentaire', $notification->getDetail());
        $this->mailManager->sendPublicationCommentNotification($notification->getUser(), $notification->getData());
    }
}
