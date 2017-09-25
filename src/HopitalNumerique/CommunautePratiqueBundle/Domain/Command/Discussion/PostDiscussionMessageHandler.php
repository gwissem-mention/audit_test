<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
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

        // @TODO: check link

        $this->entityManager->persist($message);
        $this->entityManager->flush($message);
    }
}
