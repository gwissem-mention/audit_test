<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener\Discussion;

use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\DiscussionMovedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Repository\GroupeInscriptionRepository;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\SubscriptionRepository;
use HopitalNumerique\CoreBundle\Service\ObjectIdentity\UserSubscription;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DiscussionMovedSubscriber implements EventSubscriberInterface
{
    /**
     * @var SubscriptionRepository $subscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * @var GroupeInscriptionRepository $groupeInscriptionRepository
     */
    protected $groupeInscriptionRepository;

    /**
     * @var UserSubscription $userSubscription
     */
    protected $userSubscription;

    /**
     * DiscussionMovedSubscriber constructor.
     *
     * @param SubscriptionRepository $subscriptionRepository
     * @param GroupeInscriptionRepository $groupeInscriptionRepository
     * @param UserSubscription $userSubscription
     */
    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        GroupeInscriptionRepository $groupeInscriptionRepository,
        UserSubscription $userSubscription
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->groupeInscriptionRepository = $groupeInscriptionRepository;
        $this->userSubscription = $userSubscription;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::DISCUSSION_MOVED => [
                ['subscribeUsers'],
            ],
        ];
    }

    /**
     * @param DiscussionMovedEvent $event
     */
    public function subscribeUsers(DiscussionMovedEvent $event)
    {
        $inscriptions = $this->groupeInscriptionRepository->getInscriptionsInGroup($event->getGroup());
        $subscribersDiscussion = $this->subscriptionRepository->findSubscribers(
            ObjectIdentity::createFromDomainObject($event->getDiscussion())
        );

        $inscriptionsMembers = array_map(function ($inscription) {
            return $inscription->getUser();
        }, $inscriptions);

        $unsubscribeMembers = array_diff($subscribersDiscussion, $inscriptionsMembers);

        // Unsubscribe members
        foreach ($unsubscribeMembers as $member) {
            $this->userSubscription->unsubscribe($event->getDiscussion(), $member);
        }

        // Subscribe users in discussion
        foreach ($inscriptionsMembers as $member) {
            $this->userSubscription->subscribe($event->getDiscussion(), $member);
        }
    }
}
