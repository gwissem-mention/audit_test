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

        $message->setActive(false);

        if (1 === $discussion->getMessages()->count()) {
            $discussion->setActive(false);
        }

        $this->entityManager->flush();
    }
}
