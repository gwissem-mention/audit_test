<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;

class ExcludeRiskHandler
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
     * @param ExcludeRiskCommand $command
     */
    public function handle(ExcludeRiskCommand $command)
    {
        $command->guidedSearchStep->addExcludedRisk($command->risk);

        $this->entityManager->flush();
    }
}
