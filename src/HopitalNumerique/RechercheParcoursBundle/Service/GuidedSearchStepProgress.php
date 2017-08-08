<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\RechercheParcoursBundle\Entity\RiskAnalysis;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\RechercheParcoursBundle\Repository\RiskAnalysisRepository;
use HopitalNumerique\RechercheParcoursBundle\Repository\RechercheParcoursDetailsRepository;

/**
 * Class GuidedSearchStepProgress
 */
class GuidedSearchStepProgress
{
    /**
     * Percentage from which a step is considered to be completed.
     */
    const FILLED_PERCENTAGE = 80;

    /**
     * @var Domaine
     */
    protected $currentDomain;

    /**
     * @var StepRisksFactory
     */
    protected $stepRiskFactory;

    /**
     * @var RiskAnalysisRepository
     */
    protected $riskAnalysisRepository;

    /**
     * @var RechercheParcoursDetailsRepository
     */
    protected $rechercherParcoursDetailsRepository;

    /**
     * GuidedSearchStepProgress constructor.
     *
     * @param CurrentDomaine                     $currentDomaine
     * @param StepRisksFactory                   $stepRisksFactory
     * @param RiskAnalysisRepository             $riskAnalysisRepository
     * @param RechercheParcoursDetailsRepository $rechercheParcoursDetailsRepository
     */
    public function __construct(
        CurrentDomaine $currentDomaine,
        StepRisksFactory $stepRisksFactory,
        RiskAnalysisRepository $riskAnalysisRepository,
        RechercheParcoursDetailsRepository $rechercheParcoursDetailsRepository
    ) {
        $this->currentDomain = $currentDomaine->get();
        $this->stepRiskFactory = $stepRisksFactory;
        $this->riskAnalysisRepository = $riskAnalysisRepository;
        $this->rechercherParcoursDetailsRepository = $rechercheParcoursDetailsRepository;
    }

    /**
     * Returns the steps with a  completion rate less than the FILLED_PERCENTAGE value.
     *
     * @param GuidedSearch $guidedSearch
     * @param User         $user
     *
     * @return GuidedSearchStep[]
     */
    public function getUncompletedSteps(GuidedSearch $guidedSearch, User $user)
    {
        $steps = [];

        foreach ($guidedSearch->getStepsOrderedByPath() as $step) {
            $risks = array_map(function ($risk) {
                return $risk->riskId;
            }, $this->stepRiskFactory->getStepRiskDTO($this->currentDomain, $guidedSearch, $step));

            $filledRisks = array_map(function ($riskAnalysis) {
                /** @var RiskAnalysis $riskAnalysis */
                return $riskAnalysis->getRisk()->getId();
            }, $this->riskAnalysisRepository->findByStepAndUser(
                $step,
                $user
            ));

            if (count($filledRisks) / count($risks) * 100 < GuidedSearchStepProgress::FILLED_PERCENTAGE) {
                $steps[] = $step;
            }
        }

        return $steps;
    }
}
