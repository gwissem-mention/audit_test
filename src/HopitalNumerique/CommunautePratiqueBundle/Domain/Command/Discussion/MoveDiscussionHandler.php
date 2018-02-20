<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\DiscussionMovedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class MoveDiscussionHandler
 */
class MoveDiscussionHandler
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * MoveDiscussionHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Reset groups, add to new group and move discussion
     * @param MoveDiscussionCommand $command
     */
    public function handle(MoveDiscussionCommand $command)
    {
        $discussion = $command->discussion;
        $group = $command->group;

        $discussion->resetGroups();
        $discussion->setParent(null);
        $discussion->addGroup($group);

        $this->eventDispatcher->dispatch(Events::DISCUSSION_MOVED, new DiscussionMovedEvent($discussion, $group));

        $this->entityManager->flush();
    }
}
