<?php

namespace HopitalNumerique\RechercheParcoursBundle\DTO;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\RechercheParcoursBundle\Entity\RiskAnalysis;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;

class RiskSynthesisRiskDTO
{
    /**
     * @var float $initialSkillsRate
     */
    public $initialSkillsRate;

    /**
     * @var float $currentSkillsRate
     */
    public $currentSkillsRate;

    /**
     * @var int $uncontrolledCriticalRisks
     */
    public $uncontrolledCriticalRisks = 0;

    /**
     * @var int $controlledCriticalRisks
     */
    public $controlledCriticalRisks = 0;

    /**
     * @var array $highestCriticalRisksAnalysis
     */
    public $highestCriticalRisksAnalysis = [];

    /**
     * @var string $directLink
     */
    public $directLink;

    /**
     * @param GuidedSearchStep[] $guidedSearchSteps
     * @param User|null $user
     *
     * @return RiskSynthesisRiskDTO
     */
    public static function createFromGuidedSearchSteps($guidedSearchSteps, User $user = null)
    {
        $riskSynthesisRiskDTO = new self();

        $riskSynthesisRiskDTO->initialSkillsRate = self::calculateAverageSkillsRate('initial', $guidedSearchSteps, $user);
        $riskSynthesisRiskDTO->currentSkillsRate = self::calculateAverageSkillsRate('current', $guidedSearchSteps, $user);
        $riskSynthesisRiskDTO->controlledCriticalRisks = self::countCriticalRisksControl($guidedSearchSteps, $user);
        $riskSynthesisRiskDTO->uncontrolledCriticalRisks = self::countCriticalRisksControl($guidedSearchSteps, $user, false);
        $riskSynthesisRiskDTO->highestCriticalRisksAnalysis = self::getHighestCriticalRisksAnalysis($guidedSearchSteps, $user);

        return $riskSynthesisRiskDTO;
    }

    /**
     * Count the number of critical risk analysis controller (skillsRate >= 70) or uncontroller (skillsRate < 70)
     *
     * @param GuidedSearchStep[] $guidedSearchSteps
     * @param User|null $user
     * @param bool $controlled
     *
     * @return int
     */
    private static function countCriticalRisksControl($guidedSearchSteps, User $user = null, $controlled = true)
    {
        $count = 0;
        foreach ($guidedSearchSteps as $guidedSearchStep) {
            $count += $guidedSearchStep->getRisksAnalysis()->filter(function (RiskAnalysis $riskAnalysis) use ($user, $controlled) {
                return (is_null($riskAnalysis->getOwner()) || $riskAnalysis->getOwner() === $user) &&
                    $riskAnalysis->getCriticality() >= 12 && (
                        ($controlled && $riskAnalysis->getSkillsRate() >= 70) ||
                        (!$controlled && $riskAnalysis->getSkillsRate() < 70)
                    )
                    ;
            })->count();
        }

        return $count;
    }

    /**
     * Weighted average of skills rate and criticality
     *
     * @param string $type
     * @param GuidedSearchStep[] $guidedSearchSteps
     * @param User|null $user
     *
     * @return float
     */
    private static function calculateAverageSkillsRate($type, $guidedSearchSteps, User $user = null)
    {
        $weighting = 0;
        $sum = 0;
        foreach ($guidedSearchSteps as $guidedSearchStep) {
            /** @var RiskAnalysis[] $riskAnalysis */
            $riskAnalysis = $guidedSearchStep->getRisksAnalysis()->filter(function (RiskAnalysis $riskAnalyse) use ($user) {
                return is_null($riskAnalyse->getOwner()) || $riskAnalyse->getOwner() === $user;
            });

            foreach ($riskAnalysis as $riskAnalyse) {
                if (!is_null($riskAnalyse->{sprintf('get%sSkillsRate', ucfirst($type))}())) {
                    $weighting += $riskAnalyse->getCriticality();
                    $sum += $riskAnalyse->{sprintf('get%sSkillsRate', ucfirst($type))}() * $riskAnalyse->getCriticality();
                }
            }

        }

        return $sum / ($weighting ?: 1);
    }


    /**
     * Get top 5 of most critical risk analyse
     *
     * @param GuidedSearchStep[] $guidedSearchSteps
     * @param User|null $user
     *
     * @return array
     */
    private static function getHighestCriticalRisksAnalysis($guidedSearchSteps, User $user = null)
    {
        $riskAnalysis = [];

        foreach ($guidedSearchSteps as $guidedSearchStep) {
            /** @var RiskAnalysis[] $guidedSearchStepRisks */
            $guidedSearchStepRisks = $guidedSearchStep->getRisksAnalysis()->filter(function (RiskAnalysis $riskAnalysis) use ($user) {
                return is_null($riskAnalysis->getOwner()) || $riskAnalysis->getOwner() === $user;
            });

            foreach ($guidedSearchStepRisks as $guidedSearchStepRisk) {
                $riskAnalysis[$guidedSearchStepRisk->getRisk()->getId()] = $guidedSearchStepRisk;
            }
        }

        usort($riskAnalysis, function (RiskAnalysis $a, RiskAnalysis $b) {
            $aRate = $a->getCriticality() / ($a->getSkillsRate() ?: 1);
            $bRate = $b->getCriticality() / ($b->getSkillsRate() ?: 1);

            if ($aRate === $bRate) {
                return 0;
            }
            return ($aRate < $bRate) ? 1 : -1;
        });

        array_slice($riskAnalysis, 0, 5);

        return $riskAnalysis;
    }
}
