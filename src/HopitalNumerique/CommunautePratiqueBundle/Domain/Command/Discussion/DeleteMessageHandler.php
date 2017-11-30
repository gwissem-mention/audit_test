<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use Doctrine\ORM\EntityManagerInterface;

class DeleteMessageHandler
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * DeleteMessageHandler constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param DeleteMessageCommand $command
     */
    public function handle(DeleteMessageCommand $command)
    {
        $message = $command->message;
        $discussion = $message->getDiscussion();

        $this->entityManager->remove($message);

        if ($discussion->getMessages()->first()->getId() === $message->getId()) {
            $this->entityManager->remove($discussion);
        }

        $this->entityManager->flush();
    }
}
