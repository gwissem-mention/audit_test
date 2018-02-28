<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
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
     * @return integer
     */
    public static function getNotifPosition()
    {
        return 2;
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
            NotificationProviderAbstract::normalizeDetailContent($this->processComment($comment->getMessage())),
            array_merge(
                parent::generateOptions($comment->getFiche()->getGroupe(), $comment->getUser()),
                [
                    'ficheId' => $comment->getFiche()->getId(),
                    'nomFiche' => $comment->getFiche()->getQuestionPosee(),
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
        $this->mailManager->sendCdpFormCommentNotification($notification->getUser(), $notification->getData());
    }
}
