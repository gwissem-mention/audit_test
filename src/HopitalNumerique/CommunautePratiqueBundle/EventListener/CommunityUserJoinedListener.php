<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener;

use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\NotificationBundle\Service\Notifications;
use HopitalNumerique\CommunautePratiqueBundle\Event\EnrolmentEvent;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\CoreBundle\Service\ObjectIdentity\UserSubscription;
use HopitalNumerique\NotificationBundle\EventListener\NotificationListenerAbstract;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository;
use HopitalNumerique\CommunautePratiqueBundle\Service\Notification\CommunityUserJoinedNotificationProvider;

/**
 * Class CommunityUserJoinedListener.
 *
 * @method CommunityUserJoinedNotificationProvider getProvider()
 */
class CommunityUserJoinedListener extends NotificationListenerAbstract
{
    /**
     * @var CurrentDomaine
     */
    protected $currentDomainFinder;

    /**
     * @var UserSubscription
     */
    protected $userSubscription;

    /**
     * @var DiscussionRepository
     */
    protected $discussionRepository;

    /**
     * CommunityUserJoinedListener constructor.
     *
     * @param Notifications $notificationService
     * @param CurrentDomaine $currentDomainFinder
     * @param UserSubscription $userSubscription
     * @param DiscussionRepository $discussionRepository
     */
    public function __construct(
        Notifications $notificationService,
        CurrentDomaine $currentDomainFinder,
        UserSubscription $userSubscription,
        DiscussionRepository $discussionRepository
    ) {
        parent::__construct($notificationService);

        $this->discussionRepository = $discussionRepository;
        $this->currentDomainFinder = $currentDomainFinder;
        $this->userSubscription = $userSubscription;
    }

    /**
     * @param EnrolmentEvent $event
     */
    public function onCommunityUserJoined(EnrolmentEvent $event)
    {
        if ($this->getProvider()) {
            $this->getProvider()->fire($event->getUser());
        }
    }

    /**
     * @param EnrolmentEvent $event
     */
    public function autoSubscribe(EnrolmentEvent $event)
    {
        $currentDomain = $this->currentDomainFinder->get();

        foreach ($this->discussionRepository->getPublicDiscussionForDomain($currentDomain) as $discussion) {
            $this->userSubscription->subscribe($discussion, $event->getUser());
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::ENROLL_USER => [
                ['autoSubscribe', 0],
                ['onCommunityUserJoined', 0]
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getProviderCode()
    {
        return CommunityUserJoinedNotificationProvider::getNotificationCode();
    }
}
