<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\CartBundle\Event\ReportEvent;
use HopitalNumerique\CartBundle\Events;
use HopitalNumerique\CartBundle\Repository\ReportItemRepository;
use HopitalNumerique\CartBundle\Service\ReportGenerator\ReportGenerator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GenerateReportCommandHandler
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var ReportItemRepository $reportItemRepository
     */
    protected $reportItemRepository;

    /**
     * @var ReportGenerator $reportGenerator
     */
    protected $reportGenerator;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * AddCartItemsToReportCommandHandler constructor.
     *
     * @param EntityManagerInterface   $entityManager
     * @param $reportItemRepository    $reportItemRepository
     * @param ReportGenerator          $reportGeneratorAggregator
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ReportItemRepository $reportItemRepository,
        ReportGenerator $reportGeneratorAggregator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->reportItemRepository = $reportItemRepository;
        $this->reportGenerator = $reportGeneratorAggregator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param GenerateReportCommand $command
     */
    public function handle(GenerateReportCommand $command)
    {
        if (is_null($report = $command->reportFactory->getReport())) {
            $report = new Report($command->owner);
            $this->entityManager->persist($report);
        } else {
            $report->setUpdatedAt(new \DateTime());
        }

        $report
            ->setName($command->name)
            ->setColumns($command->columns)
        ;

        $reportItems = [];
        foreach ($command->reportFactory->getFactoryItems() as $factoryItem) {
            $reportItems[$factoryItem->getItem()->getId()] = $factoryItem->getItem();
            $factoryItem->getItem()
                ->setReport($report)
                ->setPosition($factoryItem->getPosition())
            ;
        }

        foreach ($report->getItems() as $item) {
            if (!isset($reportItems[$item->getId()])) {
                $this->entityManager->remove($item);
            }
        }

        if (is_null($command->reportFactory->getReport())) {
            $this->entityManager->remove($command->reportFactory);
        }

        $this->entityManager->flush();

        $this->generateReport($report);

        if ($report) {
            /**
             * Fire 'REPORT_UPDATED' event
             */
            $event = new ReportEvent($report, $command->owner);
            $this->eventDispatcher->dispatch(Events::REPORT_UPDATED, $event);
        }
    }

    /**
     * @param Report $report
     */
    private function generateReport(Report $report)
    {
        $this->reportGenerator->generate($report);
    }
}
