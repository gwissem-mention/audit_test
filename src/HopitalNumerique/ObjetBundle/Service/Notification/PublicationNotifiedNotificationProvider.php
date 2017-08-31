<?php

namespace HopitalNumerique\ObjetBundle\Service\Notification;

use HopitalNumerique\NotificationBundle\Model\Notification;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class PublicationNotifiedNotificationProvider.
 */
class PublicationNotifiedNotificationProvider extends PublicationNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'publication_notified';

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
     * @param Objet   $object
     * @param Contenu $infradoc
     * @param string  $reason
     */
    public function fire(Objet $object, Contenu $infradoc = null, $reason = '')
    {
        if (!$infradoc) {
            $uid = $object->getId();
        } else {
            $uid = [$object->getId(), $infradoc];
        }

        $this->processNotification(
            $uid,
            $object->getTitre(),
            $reason,
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
