<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;

class IncludeRiskHandler
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * ExcludeRiskHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param IncludeRiskCommand $command
     */
    public function handle(IncludeRiskCommand $command)
    {
        $command->guidedSearchStep->removeExcludedRisk($command->risk);

        $this->entityManager->flush();
    }
}
