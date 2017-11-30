<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener\Discussion;

use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\DiscussionVisibilityEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CoreBundle\Service\ObjectIdentity\UserSubscription;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DiscussionVisibilitySubscriber implements EventSubscriberInterface
{
    /**
     * @var UserSubscription
     */
    protected $userSubscription;

    /**
     * DiscussionVisibilitySubscriber constructor.
     *
     * @param UserSubscription $userSubscription
     */
    public function __construct(UserSubscription $userSubscription) {
        $this->userSubscription = $userSubscription;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::DISCUSSION_PUBLIC => [
                ['unSubscribeUser'],
            ],
        ];
    }

    /**
     * @param DiscussionVisibilityEvent $event
     */
    public function unSubscribeUser(DiscussionVisibilityEvent $event)
    {
        $discussion = $event->getDiscussion();
        if ($discussion && !$discussion->isPublic()) {
            // Unsubscribe users if is not in group of discussion.
            $subscribers = $this->userSubscription->listSubscribed($discussion);
            foreach ($subscribers as $subscriber) {
                $user = $subscriber->getUser();
                $hasGroup = false;
                if (!empty($user) && !empty($groups = $user->getCommunautePratiqueGroupes())) {
                    foreach ($groups as $group) {
                        $hasGroup = $discussion->getGroups()->contains($group);
                    }
                }

                if (!$hasGroup) {
                    $this->userSubscription->unsubscribe($discussion, $user);
                }
            }
        }
    }
}
