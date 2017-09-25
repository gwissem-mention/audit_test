<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\ObjetBundle\Entity\Subscription;
use HopitalNumerique\ObjetBundle\Events;
use HopitalNumerique\ObjetBundle\Event\PublicationNotifiedEvent;
use HopitalNumerique\ObjetBundle\Repository\SubscriptionRepository;

/**
 * Class SubscribeToObjectHandler.
 */
class SubscribeToObjectHandler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * SubscribeToObjectHandler constructor.
     *
     * @param EntityManager $entityManager
     * @param SubscriptionRepository $subscriptionRepository
     */
    public function __construct(EntityManager $entityManager, SubscriptionRepository $subscriptionRepository)
    {
        $this->entityManager = $entityManager;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @param SubscribeToObjectCommand $command
     */
    public function handle(SubscribeToObjectCommand $command)
    {
        $subscription = $this->subscriptionRepository->findOneBy([
            'user' => $command->user,
            'objet' => $command->object,
            'contenu' => $command->content,
        ]);

        if (null === $subscription) {
            $subscription = new Subscription();

            $subscription->setObjet($command->object);
            $subscription->setContenu($command->content);
            $subscription->setUser($command->user);

            $this->entityManager->persist($subscription);
            $this->entityManager->flush($subscription);
        }
    }
}
