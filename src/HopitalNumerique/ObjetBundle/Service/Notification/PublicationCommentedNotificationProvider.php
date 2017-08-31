<?php

namespace HopitalNumerique\ObjetBundle\Service\Notification;

use HopitalNumerique\NotificationBundle\Model\Notification;
use HopitalNumerique\NotificationBundle\NotificationBundle;
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

        if (strlen($title) > NotificationBundle::LIMIT_NOTIFY_TITLE_LENGTH) {
            $title = substr($title, 0, NotificationBundle::LIMIT_NOTIFY_TITLE_LENGTH) . '...';
        }

        $detail = $comment->getContenu();
        if (strlen($detail) > NotificationBundle::LIMIT_NOTIFY_DESC_LENGTH) {
            $detail = substr($detail, 0, NotificationBundle::LIMIT_NOTIFY_DESC_LENGTH) . '...';
        }

        $this->processNotification(
            $uid,
            $title,
            $detail,
            [
                'object' => $object,
                'infradoc' => $infradoc
            ]
        );
    }

    /**
     * Checks if a notification should be stacked for user.
     * Will return true if publication user last view dateTime is older than notification dateTime.
     *
     * @param UserInterface $user
     * @param Notification $notification
     *
     * @return bool
     */
    public function canNotify(UserInterface $user, Notification $notification)
    {
        $lastViewDate = $this->consultationRepository->getUserLatestViewDate(
            $user,
            $notification->getData('object'),
            $notification->getData('infradoc')
        );

        if (null === $lastViewDate) {
            return false;
        } else {
            return new \DateTime($lastViewDate) < $notification->getDateTime();
        }
    }

    /**
     * @param UserInterface $user
     * @param Notification $notification
     */
    public function notify(UserInterface $user, Notification $notification)
    {

    }
}
