<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CartBundle\Service\ReportGenerator\ReportGenerator;

class DuplicateReportCommandHandler
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var ReportGenerator $reportGenerator
     */
    protected $reportGenerator;

    /**
     * AddCartItemsToReportCommandHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ReportGenerator $reportGenerator
     */
    public function __construct(EntityManagerInterface $entityManager, ReportGenerator $reportGenerator)
    {
        $this->entityManager = $entityManager;
        $this->reportGenerator = $reportGenerator;
    }

    /**
     * @param DuplicateReportCommand $command
     */
    public function handle(DuplicateReportCommand $command)
    {
        $clonedReport = clone $command->report;
        $clonedReport->setName($command->reportName);
        $clonedReport->setOwner($command->owner);

        $this->entityManager->persist($clonedReport);
        $this->entityManager->flush();

        $this->reportGenerator->generate($clonedReport);
    }
}
