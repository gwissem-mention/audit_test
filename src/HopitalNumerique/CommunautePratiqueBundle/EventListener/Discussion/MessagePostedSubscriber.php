<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener\Discussion;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Read;
use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\MessagePostedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\ReadRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MessagePostedSubscriber implements EventSubscriberInterface
{
    /**
     * @var ReadRepository $readRepository
     */
    protected $readRepository;

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * MessagePostedSubscriber constructor.
     *
     * @param ReadRepository $readRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ReadRepository $readRepository, EntityManagerInterface $entityManager)
    {
        $this->readRepository = $readRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::DISCUSSION_MESSAGE_POSTED => [
                ['readDiscussion', 0],
            ],
        ];
    }

    public function readDiscussion(MessagePostedEvent $event)
    {
        $message = $event->getMessage();
        $author = $message->getUser();
        $discussion = $message->getDiscussion();

        if ($read = $this->readRepository->findOneByUserAndDiscussion($author, $discussion)) {
            $read->setLastMessageDate($message->getCreatedAt());
        } else {
            $read = new Read($author, $discussion, $message->getCreatedAt());
            $this->entityManager->persist($read);
        }

        $this->entityManager->flush($read);
    }
}
