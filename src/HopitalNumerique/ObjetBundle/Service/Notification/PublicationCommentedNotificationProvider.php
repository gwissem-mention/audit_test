<?php

namespace HopitalNumerique\ObjetBundle\Service\Notification;

use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Enum\NotificationLimitTextLengthEnum;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use HopitalNumerique\ObjetBundle\Entity\Commentaire;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;

/**
 * Class PublicationCommentedNotificationProvider.
 */
class PublicationCommentedNotificationProvider extends PublicationNotificationProviderAbstract
{
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
     * @return integer
     */
    public static function getNotifPosition()
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribers(Notification $notification)
    {
        return $this->subscriptionRepository->getSubscribersQueryBuilder(
            $notification->getCreatedAt(),
            $notification->getData('idPublication'),
            $notification->getData('idInfradoc'),
            $notification->getData('author')
        );
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

        $this->processNotification(
            $uid,
            $title,
            NotificationProviderAbstract::normalizeDetailContent($comment->getTexte()),
            array_merge(
                parent::generateOptions($object, $infradoc),
                [
                    'author' => $comment->getUser()->getId(),
                ]
            )
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
