<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Document;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class GroupDocumentCreatedNotificationProvider.
 */
class GroupDocumentCreatedNotificationProvider extends PracticeCommunityHelpGroupsNotificationProviderAbstract
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
     * @return integer
     */
    public static function getNotifPosition()
    {
        return 5;
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
            $group->getTitre() . ' - ' . $document->getUser()->getPrenomNom(),
            null,
            array_merge(
                parent::generateOptions($group, $document->getUser()),
                [
                    'nomFichier' => $document->getNom(),
                ]
            )
        );
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
        $this->mailManager->sendCdpGroupDocumentNotification($notification->getUser(), $notification->getData());
    }
}
