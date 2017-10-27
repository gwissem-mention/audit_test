<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener\Discussion;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Read;
use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\DiscussionCreatedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\MessagePostedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\ReadRepository;
use HopitalNumerique\CoreBundle\Service\ObjectIdentity\UserSubscription;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DiscussionCreatedSubscriber implements EventSubscriberInterface
{
    /**
     * @var UserSubscription
     */
    protected $userSubscription;

    /**
     * DiscussionCreatedSubscriber constructor.
     *
     * @param UserSubscription $userSubscription
     */
    public function __construct(UserSubscription $userSubscription)
    {
        $this->userSubscription = $userSubscription;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::DISCUSSION_CREATED => [
                ['subscribe', 0],
            ],
        ];
    }

    /**
     * Auto subscribe user to the created discussion
     *
     * @param DiscussionCreatedEvent $event
     */
    public function subscribe(DiscussionCreatedEvent $event)
    {
        $this->userSubscription->subscribe($event->getDiscussion(), $event->getDiscussion()->getUser());
    }
}
