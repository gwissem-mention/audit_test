<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Event\EnrolmentEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DisenrollUserHandler
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
     * Disenroll user from communauté de pratique
     *
     * @param DisenrollUserCommand $command
     */
    public function handle(DisenrollUserCommand $command)
    {
        $command->user->setInscritCommunautePratique(false);
        $this->entityManager->flush($command->user);

        $this->eventDispatcher->dispatch(Events::DISENROLL_USER, new EnrolmentEvent($command->user));
    }
}
