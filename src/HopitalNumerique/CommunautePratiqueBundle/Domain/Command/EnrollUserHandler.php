<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Event\EnrolmentEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EnrollUserHandler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * EnrollUserHandler constructor.
     *
     * @param EntityManager $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EntityManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Enroll user to Communauté de pratique
     *
     * @param EnrollUserCommand $command
     */
    public function handle(EnrollUserCommand $command)
    {
        $command->user
            ->setInscritCommunautePratique(true)
            ->setCommunautePratiqueEnrollmentDate(new \DateTime())
        ;
        $this->entityManager->flush($command->user);

        $this->eventDispatcher->dispatch(Events::ENROLL_USER, new EnrolmentEvent($command->user));
    }
}
