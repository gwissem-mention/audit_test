<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener\Discussion;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Viewed;
use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\DiscussionViewedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DiscussionViewSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * DiscussionViewSubscriber constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::DISCUSSION_VIEWED => [
                ['incrementedViewCount'],
            ],
        ];
    }

    /**
     * @param DiscussionViewedEvent $event
     */
    public function incrementedViewCount(DiscussionViewedEvent $event)
    {
        $discussion = $event->getDiscussion();
        $user = $event->getUser();

        $viewed = new Viewed($discussion, $user);
        $this->entityManager->persist($viewed);
        $this->entityManager->flush();
    }
}
