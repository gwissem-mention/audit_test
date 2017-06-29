<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CartBundle\Entity\ReportSharing;
use HopitalNumerique\CartBundle\Exception\CannotRemoveCopyException;

class RemoveSharingReportCommandHandler
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * RemoveSharingReportCommandHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param RemoveSharingReportCommand $command
     */
    public function handle(RemoveSharingReportCommand $command)
    {
        if ($command->reportSharing->getType() === ReportSharing::TYPE_COPY) {
            throw new CannotRemoveCopyException();
        }

        $this->entityManager->remove($command->reportSharing);

        $this->entityManager->flush();
    }
}
