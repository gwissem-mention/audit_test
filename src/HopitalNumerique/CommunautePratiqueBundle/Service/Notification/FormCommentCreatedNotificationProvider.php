<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;
use HopitalNumerique\NotificationBundle\Model\Notification;
use HopitalNumerique\NotificationBundle\NotificationBundle;
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
                NotificationBundle::LIMIT_NOTIFY_DESC_LENGTH
            ),
            ['comment' => $comment]
        );
    }

    /**
     * Checks if a notification should be stacked for user.
     * Will return true in all cases.
     *
     * @param UserInterface $user
     * @param Notification $notification
     *
     * @return bool
     */
    public function canNotify(UserInterface $user, Notification $notification)
    {
        return true;
    }

    /**
     * @param UserInterface $user
     * @param Notification $notification
     */
    public function notify(UserInterface $user, Notification $notification)
    {

    }
}
