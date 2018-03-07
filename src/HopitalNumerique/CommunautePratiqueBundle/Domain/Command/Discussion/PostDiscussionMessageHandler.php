<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\MessageCreatedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\MessagePostedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\MessageRepository;
use HopitalNumerique\FichierBundle\Entity\File;
use HopitalNumerique\FichierBundle\Repository\FileRepository;
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
     * @var FileRepository $fileRepository
     */
    protected $fileRepository;

    /**
     * @var MessageRepository $messageRepository
     */
    protected $messageRepository;

    /**
     * PostDiscussionMessageHandler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityManagerInterface $entityManager
     * @param FileRepository $fileRepository
     * @param MessageRepository $messageRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
        FileRepository $fileRepository,
        MessageRepository $messageRepository
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
        $this->fileRepository = $fileRepository;
        $this->messageRepository = $messageRepository;
    }

    /**
     * @param PostDiscussionMessageCommand $command
     *
     * @return Message|null
     */
    public function handle(PostDiscussionMessageCommand $command)
    {
        if (null !== $command->message) {
            $message = $command->message;
            $message->setContent($command->content);
            $isNew = false;
        } else {
            $message = new Message($command->discussion, $command->content, $command->author);
            if ($command->createdAt) {
                $message->setCreatedAt($command->createdAt);
            }
            $isNew = true;
            $this->entityManager->persist($message);
        }

        if (!empty($command->biography)) {
            $command->author
                ->setPresentation($command->biography)
                ->setDateLastUpdate(new \DateTime())
            ;

            $this->entityManager->flush($command->author);
        }

        /** @var File $file */
        foreach ($this->fileRepository->findById($command->files) as $file) {
            $file->setActive(true);
            $message->addFile($file);
        }

        if ($command->isFirstMessage) {
            $this->entityManager->flush($command->discussion);
        }

        $this->entityManager->flush($message);

        $this->eventDispatcher->dispatch(Events::DISCUSSION_MESSAGE_POSTED, new MessagePostedEvent($message));
        if ($isNew) {
            if (!$command->isFirstMessage) {
                $this->eventDispatcher->dispatch(Events::DISCUSSION_MESSAGE_CREATED, new MessageCreatedEvent($message));
            }
        }

        $this->entityManager->flush($message);

        return $message;
    }
}
