<?php

namespace Nodevo\MailBundle\EventListener;

use Nodevo\MailBundle\Entity\RecommendationMailLog;
use Nodevo\MailBundle\NodevoMailEvents;
use Doctrine\ORM\EntityManagerInterface;
use Nodevo\MailBundle\Event\RecommendationLoggerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RecommendationMailSendedSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * RecommendationSendedSubscriber constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            NodevoMailEvents::RECOMMENDATION_SENDED => [
                ['logRecommendation', 0],
            ],
        ];
    }

    /**
     * @param RecommendationLoggerEvent $event
     */
    public function logRecommendation(RecommendationLoggerEvent $event)
    {
        if (is_null($event->getUser())) {
            return;
        }

        $log = new RecommendationMailLog($event->getRecipientEmail(), $event->getUser());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
