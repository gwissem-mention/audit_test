<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service\Risk\Export;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Translation\TranslatorInterface;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\RechercheParcoursBundle\Entity\RiskAnalysis;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails;

/**
 * Class SynthesisExport
 */
abstract class SynthesisExport
{
    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * SynthesisExport constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Output CSV or Excel file
     *
     * @param GuidedSearch $guidedSearch
     * @param User|null $user
     */
    abstract public function exportGuidedSearch(GuidedSearch $guidedSearch, User $user = null);

    /**
     * Get formatted risk analysis parsed in array ready to be exported
     *
     * @param GuidedSearch $guidedSearch
     * @param User|null $user
     *
     * @return array
     */
    protected function parseGuidedSearch(GuidedSearch $guidedSearch, User $user = null)
    {
        $risks = [];
        foreach ($guidedSearch->getSteps() as $step) {
            $stepPath = explode(':', $step->getStepPath());

            /** @var RiskAnalysis[] $riskAnalysis */
            $riskAnalysis = $step->getRisksAnalysis()->filter(function (RiskAnalysis $riskAnalysis) use ($user) {
                return $riskAnalysis->getOwner() === $user;
            })->toArray();

            usort($riskAnalysis, function (RiskAnalysis $a, RiskAnalysis $b) {
                if ($a->getCriticality() === $b->getCriticality()) {
                    return 0;
                }

                return ($a->getCriticality() < $b->getCriticality()) ? 1 : -1;
            });

            foreach ($riskAnalysis as $riskAnalyse) {
                $risks[] = [
                    $this->getStepLabel($guidedSearch, $stepPath[0]),
                    isset($stepPath[1]) ? $this->getSubStepLabel($guidedSearch, $stepPath[0], $stepPath[1]) : null,
                    $riskAnalyse->getRisk()->getNature()->getLibelle(),
                    $riskAnalyse->getRisk()->getLabel(),
                    $riskAnalyse->getCriticality() ? $riskAnalyse->getCriticality() : '',
                    $riskAnalyse->getInitialSkillsRate(),
                    $riskAnalyse->getCurrentSkillsRate(),
                ];
            }
        }

        return $risks;
    }

    /**
     * Get the guidedSearchReference label by the guidedSearchStep stepPath
     *
     * @param GuidedSearch $guidedSearch
     * @param $stepId
     *
     * @return string
     */
    private function getStepLabel(GuidedSearch $guidedSearch, $stepId)
    {
        return $guidedSearch->getGuidedSearchReference()->getRecherchesParcoursDetails()->filter(function (RechercheParcoursDetails $guidedSearchReference) use ($stepId) {
            return $guidedSearchReference->getId() === (int) $stepId;
        })->first()->getReference()->getLibelle();
    }

    /**
     * Get the guidedSearchReference child reference label by the guidedSearchStep stepPath
     *
     * @param GuidedSearch $guidedSearch
     * @param $stepId
     * @param $subStepId
     *
     * @return string
     */
    private function getSubStepLabel(GuidedSearch $guidedSearch, $stepId, $subStepId)
    {
        /** @var RechercheParcoursDetails $parent */
        $parent = $guidedSearch->getGuidedSearchReference()->getRecherchesParcoursDetails()->filter(function (RechercheParcoursDetails $guidedSearchReference) use ($stepId) {
            return $guidedSearchReference->getId() === (int) $stepId;
        })->first();

        if ($parent->getShowChildren() && $parent->getReference()->getEnfants()->count()) {
            return $parent->getReference()->getEnfants()->filter(function (Reference $reference) use ($subStepId) {
                return $reference->getId() === (int) $subStepId;
            })->first()->getLibelle();
        }

        return null;
    }

    /**
     * @return array
     */
    protected function getHeader()
    {
        return [
            $this->translator->trans('step.risks.header.step', [], 'guided_search'),
            $this->translator->trans('step.risks.header.sub_step', [], 'guided_search'),
            $this->translator->trans('step.risks.header.nature', [], 'guided_search'),
            $this->translator->trans('step.risks.header.label', [], 'guided_search'),
            $this->translator->trans('step.risks.header.criticality', [], 'guided_search'),
            $this->translator->trans('step.risks.header.initialSkills', [], 'guided_search'),
            $this->translator->trans('step.risks.header.currentSkills', [], 'guided_search'),
        ];
    }
}
