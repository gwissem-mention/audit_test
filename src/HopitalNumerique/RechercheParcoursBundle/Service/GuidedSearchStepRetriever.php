<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
use HopitalNumerique\RechercheParcoursBundle\Repository\GuidedSearchStepRepository;

class GuidedSearchStepRetriever
{
    /**
     * @var GuidedSearchStepRepository $guidedSearchStepRepository
     */
    protected $guidedSearchStepRepository;

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * GuidedSearchStepRetriever constructor.
     *
     * @param GuidedSearchStepRepository $guidedSearchStepRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(GuidedSearchStepRepository $guidedSearchStepRepository, EntityManagerInterface $entityManager)
    {
        $this->guidedSearchStepRepository = $guidedSearchStepRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param GuidedSearch $guidedSearch
     * @param $stepPath
     *
     * @return GuidedSearchStep
     */
    public function retrieveGuidedSearchStep(GuidedSearch $guidedSearch, $stepPath)
    {
        $guidedSearchStep = $this->guidedSearchStepRepository->findOneByGuidedSearchAndPath($guidedSearch, $stepPath);

        if (is_null($guidedSearchStep)) {
            $guidedSearchStep = $this->createNewOne($guidedSearch, $stepPath);
        }

        return $guidedSearchStep;
    }

    /**
     * @param RechercheParcoursGestion $guidedSearchConfig
     * @param string $stepPath
     *
     * @return GuidedSearchStep
     */
    private function createNewOne(GuidedSearch $guidedSearch, $stepPath)
    {
        $guidedSearchStep = new GuidedSearchStep($guidedSearch, $stepPath);

        $this->entityManager->persist($guidedSearchStep);
        $this->entityManager->flush($guidedSearchStep);

        return $guidedSearchStep;
    }
}
