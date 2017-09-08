<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CartBundle\Entity\ReportFactory;
use HopitalNumerique\CartBundle\Entity\ReportSharing;
use HopitalNumerique\CartBundle\Repository\ReportFactoryRepository;
use HopitalNumerique\CartBundle\Service\ReportGenerator\ReportGenerator;

class RemoveReportCommandHandler
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var ReportFactoryRepository $reportFactoryRepository
     */
    protected $reportFactoryRepository;

    /**
     * @var ReportGenerator $reportGenerator
     */
    protected $reportGenerator;

    /**
     * AddCartItemsToReportCommandHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ReportFactoryRepository $reportFactoryRepository
     * @param ReportGenerator $reportGenerator
     */
    public function __construct(EntityManagerInterface $entityManager, ReportFactoryRepository $reportFactoryRepository, ReportGenerator $reportGenerator)
    {
        $this->entityManager = $entityManager;
        $this->reportFactoryRepository = $reportFactoryRepository;
        $this->reportGenerator = $reportGenerator;
    }

    /**
     * @param RemoveReportCommand $command
     */
    public function handle(RemoveReportCommand $command)
    {
        $report = $command->report;

        if ($report->getOwner() === $command->owner) {
            $this->reportGenerator->removeReport($report);

            /** @var ReportFactory[] $reportsFactories */
            $reportsFactories = $this->reportFactoryRepository->findByReport($report);

            foreach ($reportsFactories as $reportFactory) {
                foreach ($reportFactory->getFactoryItems() as $factoryItem) {
                    $this->entityManager->remove($factoryItem->getItem());
                }
                $this->entityManager->remove($reportFactory);
            }

            foreach ($report->getItems() as $item) {
                $this->entityManager->remove($item);
            }

            foreach ($report->getShares() as $share) {
                if ($share->getCopiedReport()) {
                    $share->getCopiedReport()->setSharedBy(null);
                }
            }
            $this->entityManager->remove($report);
        } else {
            $this->entityManager->remove($report->getShares()->filter(function (ReportSharing $reportSharing) use ($command) {
                return $reportSharing->getTarget() === $command->owner;
            })->first());
        }

        $this->entityManager->flush();
    }
}
