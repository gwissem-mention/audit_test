<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CartBundle\Repository\ReportFactoryItemRepository;

class RemoveItemFromReportCommandHandler
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var ReportFactoryItemRepository $reportFactoryItemRepository
     */
    protected $reportFactoryItemRepository;

    /**
     * AddCartItemsToReportCommandHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ReportFactoryItemRepository $reportFactoryItemRepository
     */
    public function __construct(EntityManagerInterface $entityManager, ReportFactoryItemRepository $reportFactoryItemRepository)
    {
        $this->entityManager = $entityManager;
        $this->reportFactoryItemRepository = $reportFactoryItemRepository;
    }

    /**
     * @param RemoveItemFromReportCommand $command
     */
    public function handle(RemoveItemFromReportCommand $command)
    {
        $this->reportFactoryItemRepository->removeByReportItemAndOwner($command->reportItem, $command->owner);
        if (is_null($command->reportItem->getReport())) {
            $this->entityManager->remove($command->reportItem);
        }

        $this->entityManager->flush();
    }
}
