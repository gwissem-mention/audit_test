<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CartBundle\Entity\Item\ReportFactoryItem;

class ReorderReportFactoryItemsCommandHandler
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;
    
    /**
     * ReorderReportFactoryItemsCommandHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param ReorderReportFactoryItemsCommand $command
     */
    public function handle(ReorderReportFactoryItemsCommand $command)
    {
        foreach ($command->itemsOrder as $position => $itemId) {
            /** @var ReportFactoryItem $reportFactoryItem */
            $reportFactoryItem = $command->reportFactory->getFactoryItems()->filter(function (ReportFactoryItem $reportFactoryItem) use ($itemId) {
                return $reportFactoryItem->getItem()->getId() == $itemId;
            })->first();

            $reportFactoryItem->setPosition($position);
        }

         $this->entityManager->flush();
    }
}
