<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;

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
     * CreateDiscussionHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param PostDiscussionMessageHandler $postMessageHandler
     */
    public function __construct(EntityManagerInterface $entityManager, PostDiscussionMessageHandler $postMessageHandler)
    {
        $this->entityManager = $entityManager;
        $this->postMessageHandler = $postMessageHandler;
    }

    public function handle(CreateDiscussionCommand $command)
    {
        $discussion = new Discussion($command->title, $command->author, $command->domains);
        if ($command->group) {

        } else {
            $discussion->setPublic(true);
        }

        $this->entityManager->persist($discussion);
        $this->entityManager->flush($discussion);

        $this->postMessageHandler->handle(new PostDiscussionMessageCommand($discussion, $command->author, $command->content));

        return $discussion;
    }
}
