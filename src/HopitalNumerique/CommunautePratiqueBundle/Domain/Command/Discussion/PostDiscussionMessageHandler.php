<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\MessagePostedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostDiscussionMessageHandler
{
    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * PostDiscussionMessageHandler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, EntityManagerInterface $entityManager)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
    }

    /**
     * @param PostDiscussionMessageCommand $command
     */
    public function handle(PostDiscussionMessageCommand $command)
    {
        $message = new Message($command->discussion, $command->content, $command->author);

        $this->entityManager->persist($message);

        $this->eventDispatcher->dispatch(Events::DISCUSSION_MESSAGE_POSTED, new MessagePostedEvent($message));

        $this->entityManager->flush($message);
    }
}
