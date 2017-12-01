<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\ObjetBundle\Repository\SubscriptionRepository;

/**
 * Class UnsubscribeToObjectHandler.
 */
class UnsubscribeToObjectHandler
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
     * UnsubscribeToObjectHandler constructor.
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
     * @param UnsubscribeToObjectCommand $command
     */
    public function handle(UnsubscribeToObjectCommand $command)
    {
        $subscription = $this->subscriptionRepository->findOneBy([
            'user' => $command->user,
            'objet' => $command->object,
            'contenu' => $command->content,
        ]);

        if (null !== $subscription) {
            $this->entityManager->remove($subscription);
            $this->entityManager->flush($subscription);
        }
    }
}
