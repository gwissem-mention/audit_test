<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;

class RemoveReportFactoryCommandHandler
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;


    /**
     * AddCartItemsToReportCommandHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param RemoveReportFactoryCommand $command
     */
    public function handle(RemoveReportFactoryCommand $command)
    {
        $this->entityManager->remove($command->reportFactory);

        $this->entityManager->flush();
    }
}
