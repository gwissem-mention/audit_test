<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\ObjetBundle\Entity\ObjectUpdate;
use HopitalNumerique\ObjetBundle\Events;
use HopitalNumerique\ObjetBundle\Event\PublicationNotifiedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class AddObjectUpdateCommand.
 */
class AddObjectUpdateHandler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * AddObjectUpdateHandler constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param AddObjectUpdateCommand $command
     */
    public function handle(AddObjectUpdateCommand $command)
    {
        $objectUpdate = new ObjectUpdate();

        $objectUpdate->setObject($command->object);
        $objectUpdate->setContenu($command->contenu);
        $objectUpdate->setUser($command->user);
        $objectUpdate->setReason($command->reason);

        $command->object->addUpdate($objectUpdate);

        $this->entityManager->flush($command->object);

        /**
         * Fire 'PUBLICATION_NOTIFIED' event
         */
        $event = new PublicationNotifiedEvent($command->object, $command->contenu, $command->reason);
        $this->eventDispatcher->dispatch(Events::PUBLICATION_NOTIFIED, $event);
    }
}
