<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Read;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\MessageRepository;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\ReadRepository;

class ReadMessageHandler
{
    /**
     * @var ReadRepository $readRepository
     */
    protected $readRepository;

    /**
     * @var MessageRepository $messageRepository
     */
    protected $messageRepository;

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * ReadMessageHandler constructor.
     *
     * @param ReadRepository $readRepository
     * @param MessageRepository $messageRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ReadRepository $readRepository, MessageRepository $messageRepository, EntityManagerInterface $entityManager)
    {
        $this->readRepository = $readRepository;
        $this->messageRepository = $messageRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param ReadMessageCommand $command
     *
     * @throws EntityNotFoundException
     */
    public function handle(ReadMessageCommand $command)
    {
        /** @var Message $message */
        if (null === ($message = $this->messageRepository->find($command->messageId))) {
            throw new EntityNotFoundException();
        }

        /** @var Read $read */
        $read = $this->readRepository->findOneBy([
            'user' => $command->user,
            'discussion' => $message->getDiscussion(),
        ]);

        if (null === $read) {
            $read = new Read($command->user, $message->getDiscussion(), $message->getCreatedAt());
            $this->entityManager->persist($read);
        } else {
            $read->setLastMessageDate($message->getCreatedAt());
        }

        $this->entityManager->flush($read);
    }
}
