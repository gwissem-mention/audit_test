<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\ObjetBundle\Entity\ObjectUpdate;

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
     * AddObjectUpdateHandler constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param AddObjectUpdateCommand $command
     */
    public function handle(AddObjectUpdateCommand $command)
    {
        $objectUpdate = new ObjectUpdate();

        $objectUpdate->setObject($command->object);
        $objectUpdate->setUser($command->user);
        $objectUpdate->setReason($command->reason);

        $command->object->addUpdate($objectUpdate);

        $this->entityManager->flush($command->object);
    }
}
