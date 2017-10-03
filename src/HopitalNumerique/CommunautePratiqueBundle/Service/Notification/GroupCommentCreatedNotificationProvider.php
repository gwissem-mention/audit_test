<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;
use HopitalNumerique\NotificationBundle\Entity\Notification;
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
     * Submits notification to Notification manager service via FIRE_NOTIFICATION event.
     *
     * @param Commentaire $comment
     */
    public function fire(Commentaire $comment)
    {
        $this->processNotification(
            [$comment->getId()],
            $comment->getGroupe()->getTitre() . ' - ' . $comment->getUser()->getPrenomNom(),
            $this->processComment(
                $comment->getMessage(),
                self::getLimitNotifyDetailLength()
            ),
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
