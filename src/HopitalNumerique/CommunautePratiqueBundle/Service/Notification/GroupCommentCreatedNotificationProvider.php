<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class GroupCommentCreatedNotificationProvider.
 */
class GroupCommentCreatedNotificationProvider extends PracticeCommunityNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'practice_community_group_comment_created';

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
     * @return string
     */
    public static function getSectionParent()
    {
        return '';
    }

    /**
     * Submits notification to Notification manager service via FIRE_NOTIFICATION event.
     *
     * @param Commentaire $comment
     */
    public function fire(Commentaire $comment)
    {
        $this->processNotification(
            [$comment->getId()],
            $comment->getGroupe()->getTitre() . ' - ' . $comment->getUser()->getPrenomNom(),
            NotificationProviderAbstract::normalizeDetailContent($this->processComment($comment->getMessage())),
            parent::generateOptions($comment->getGroupe(), $comment->getUser())
        );
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
        $notification->addData('commentaire', $notification->getDetail());
        $this->mailManager->sendCdpGroupCommentNotification($notification->getUser(), $notification->getData());
    }
}
