<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Document;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\NotificationBundle\Entity\Notification;
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
            $group->getTitre() . ' - ' . $document->getNom() . ' - ' . $document->getUser()->getPrenomNom(),
            null,
            ['groupId' => $group->getId()]
        );
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {

    }
}
