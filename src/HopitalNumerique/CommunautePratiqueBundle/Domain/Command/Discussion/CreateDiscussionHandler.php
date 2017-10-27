<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\DiscussionCreatedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateDiscussionHandler
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var PostDiscussionMessageHandler $postMessageHandler
     */
    protected $postMessageHandler;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * CreateDiscussionHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param PostDiscussionMessageHandler $postMessageHandler
     * @param EventDispatcherInterface
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        PostDiscussionMessageHandler $postMessageHandler,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->postMessageHandler = $postMessageHandler;
    }

    /**
     * @param CreateDiscussionCommand $command
     *
     * @return Discussion
     */
    public function handle(CreateDiscussionCommand $command)
    {
        $discussion = new Discussion($command->title, $command->author, $command->domains);
        $discussion->setCreationPosition($command->source);
        if ($command->group) {
            $discussion
                ->addGroup($command->group)
                ->setPublic(false)
            ;
        }
        if ($command->object) {
            $discussion->setRelatedObject($command->object);
        }

        $this->entityManager->persist($discussion);
        $this->entityManager->flush($discussion);

        $this->eventDispatcher->dispatch(Events::DISCUSSION_CREATED, new DiscussionCreatedEvent($discussion));

        $messageCommand = new PostDiscussionMessageCommand($discussion, $command->author);
        $messageCommand->content = $command->content;
        $messageCommand->files = $command->files;
        $this->postMessageHandler->handle($messageCommand);

        return $discussion;
    }
}
