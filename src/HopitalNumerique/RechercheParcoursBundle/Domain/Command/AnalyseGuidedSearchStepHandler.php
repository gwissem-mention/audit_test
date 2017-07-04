<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;

class AnalyseGuidedSearchStepHandler
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * AnalyseGuidedSearchStepHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param AnalyseGuidedSearchStepCommand $command
     */
    public function handle(AnalyseGuidedSearchStepCommand $command)
    {
        $command->guidedSearchStep->setAnalyzed(true);

        $this->entityManager->flush();
    }
}
