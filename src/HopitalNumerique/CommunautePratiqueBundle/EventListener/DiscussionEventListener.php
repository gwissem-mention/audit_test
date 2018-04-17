<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CoreBundle\Event\UserSubscribeEvent;
use HopitalNumerique\CoreBundle\Event\UserUnsubscribeEvent;
use HopitalNumerique\CoreBundle\Service\Log;
use HopitalNumerique\CoreBundle\Service\ObjectIdentity\UserSubscription;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DiscussionEventListener implements EventSubscriberInterface
{
    /** @var Log */
    protected $logger;

    /**
     * DiscussionEventListener constructor.
     *
     * @param Log $logger
     */
    public function __construct(Log $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            UserSubscription::SUBSCRIBE => 'onUserSubscribeDiscussion',
            UserSubscription::UNSUBSCRIBE => 'onUserUnsubscribeDiscussion',
        ];
    }

    public function onUserSubscribeDiscussion(UserSubscribeEvent $event)
    {
        if ($event->getObject() instanceof Discussion) {
            $this->logger->Logger(
                'inscript',
                $event->getObject(),
                $this->getDiscussionTitle($event->getObject()),
                Discussion::class,
                $event->getUser()
            );
        }
    }

    public function onUserUnsubscribeDiscussion(UserUnsubscribeEvent $event)
    {
        if ($event->getObject() instanceof Discussion) {
            $this->logger->Logger(
                'desinscr',
                $event->getObject(),
                $this->getDiscussionTitle($event->getObject()),
                Discussion::class,
                $event->getUser()
            );
        }
    }

    /**
     * Generate log discussion title.
     *
     * @param Discussion $discussion
     *
     * @return string
     */
    protected function getDiscussionTitle(Discussion $discussion)
    {
        $titles = array_merge(
            array_map(function (Groupe $group) {
                return sprintf('[%s]', $group->getTitre());
            }, $discussion->getGroups()->toArray()),
            [$discussion->getTitle()]
        );
        return implode(' ', $titles);
    }
}
