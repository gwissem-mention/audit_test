<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\MessagePostedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Service\Discussion\ReplaceMessageFileLink;
use HopitalNumerique\FichierBundle\Entity\File;
use HopitalNumerique\FichierBundle\Repository\FileRepository;
use HopitalNumerique\UserBundle\Event\UserEvent;
use HopitalNumerique\UserBundle\UserEvents;
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
     * @var ReplaceMessageFileLink $replaceFileLink
     */
    protected $replaceFileLink;

    /**
     * PostDiscussionMessageHandler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityManagerInterface $entityManager
     * @param FileRepository $fileRepository
     * @param ReplaceMessageFileLink $replaceFileLink
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
        FileRepository $fileRepository,
        ReplaceMessageFileLink $replaceFileLink
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
        $this->fileRepository = $fileRepository;
        $this->replaceFileLink = $replaceFileLink;
    }

    /**
     * @param PostDiscussionMessageCommand $command
     */
    public function handle(PostDiscussionMessageCommand $command)
    {
        if (null !== $command->message) {
            $message = $command->message;
            $message->setContent($command->content);
        } else {
            $message = new Message($command->discussion, $command->content, $command->author);
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

        $this->entityManager->flush($message);

        $this->replaceFileLink->replaceFilesLink($message);

        $this->eventDispatcher->dispatch(Events::DISCUSSION_MESSAGE_POSTED, new MessagePostedEvent($message));

        $this->entityManager->flush($message);
    }
}
