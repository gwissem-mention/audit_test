<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\DiscussionCreatedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\UserBundle\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
     * @var UserRepository $userRepository
     */
    protected $userRepository;

    /**
     * @var TokenStorageInterface $tokenStorage
     */
    protected $tokenStorage;

    /**
     * CreateDiscussionHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param PostDiscussionMessageHandler $postMessageHandler
     * @param EventDispatcherInterface $eventDispatcher
     * @param UserRepository $userRepository
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        PostDiscussionMessageHandler $postMessageHandler,
        EventDispatcherInterface $eventDispatcher,
        UserRepository $userRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->postMessageHandler = $postMessageHandler;
        $this->userRepository = $userRepository;
        $this->tokenStorage = $tokenStorage;
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

        $this->eventDispatcher->dispatch(Events::DISCUSSION_CREATED, new DiscussionCreatedEvent(
            $discussion,
            $command->group
        ));

        $messageCommand = new PostDiscussionMessageCommand($discussion, $command->author);
        $messageCommand->content = $command->content;
        $messageCommand->createdAt = $discussion->getCreatedAt();
        $messageCommand->files = $command->files;
        $this->postMessageHandler->handle($messageCommand);

        return $discussion;
    }
}
