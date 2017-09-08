<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class FormCommentCreatedNotificationProvider.
 */
class FormCommentCreatedNotificationProvider extends PracticeCommunityNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'practice_community_form_comment_created';

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
            $comment->getFiche()->getGroupe()->getTitre() . ' - ' .
                $comment->getFiche()->getQuestionPosee() . ' - ' .
                $comment->getUser()->getPrenomNom(),
            $this->processComment(
                $comment->getMessage(),
                self::getLimitNotifyDetailLength()
            ),
            ['groupId' => $comment->getFiche()->getGroupe()->getId()]
        );
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {

    }
}
