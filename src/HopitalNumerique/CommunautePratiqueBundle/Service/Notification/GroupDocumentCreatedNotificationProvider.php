<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Document;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\NotificationBundle\Model\Notification;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class GroupDocumentCreatedNotificationProvider.
 */
class GroupDocumentCreatedNotificationProvider extends PracticeCommunityNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'practice_community_group_document_created';

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
     * @param Groupe $group
     * @param Document $document
     */
    public function fire(Groupe $group, Document $document)
    {
        $this->processNotification(
            $document->getId(),
            $group->getTitre() . ' - ' . $document->getNom() . ' - ' . $document->getUser()->getPrenomNom()
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
