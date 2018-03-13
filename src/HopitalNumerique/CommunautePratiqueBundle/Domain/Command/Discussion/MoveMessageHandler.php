<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class MoveDiscussionHandler
 */
class MoveMessageHandler
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
     *
     * @param MoveMessageCommand $command
     */
    public function handle(MoveMessageCommand $command)
    {
        $message = $command->message;
        $discussion = $command->discussion;

        $message->setDiscussion($discussion);
        $this->entityManager->flush();
    }
}
