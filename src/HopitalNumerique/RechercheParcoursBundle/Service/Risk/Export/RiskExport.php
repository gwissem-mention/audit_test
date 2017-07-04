<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service\Risk\Export;

use HopitalNumerique\ObjetBundle\Entity\RelatedRisk;
use HopitalNumerique\RechercheParcoursBundle\DTO\StepRiskDTO;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;
use Symfony\Component\Translation\TranslatorInterface;

abstract class RiskExport
{
    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * RiskExport constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param GuidedSearchStep $guidedSearchStep
     * @param StepRiskDTO[] $risks
     */
    abstract public function exportGuidedSearchStepRisks(GuidedSearchStep $guidedSearchStep, $risks);

    /**
     * Reorder risks by criticality DESC
     *
     * @param StepRiskDTO[] $risks
     */
    protected function reorderRisks(&$risks)
    {
        usort($risks, function (StepRiskDTO $a, StepRiskDTO $b) {
            $criticalityA = $a->probability * $a->impact;
            $criticalityB = $b->probability * $b->impact;

            if ($criticalityA === $criticalityB) {
                return 0;
            }
            return ($criticalityA < $criticalityB) ? 1 : -1;
        });
    }

    /**
     * @return array
     */
    protected function getHeader()
    {
        return [
            $this->translator->trans('step.risks.header.nature', [], 'guided_search'),
            $this->translator->trans('step.risks.header.label', [], 'guided_search'),
            $this->translator->trans('step.risks.header.probability', [], 'guided_search'),
            $this->translator->trans('step.risks.header.impact', [], 'guided_search'),
            $this->translator->trans('step.risks.header.criticality', [], 'guided_search'),
            $this->translator->trans('step.risks.header.initialSkills', [], 'guided_search'),
            $this->translator->trans('step.risks.header.currentSkills', [], 'guided_search'),
            $this->translator->trans('step.risks.header.relatedResourcesSL', [], 'guided_search'),
            $this->translator->trans('step.risks.header.comment', [], 'guided_search'),
        ];
    }

    /**
     * @param StepRiskDTO $risk
     *
     * @return array
     */
    protected function getDisplayableResources(StepRiskDTO $risk)
    {
        $relatedRisks = [];
        /** @var RelatedRisk $relatedRisk */
        foreach ($risk->relatedRisks as $relatedRisk) {
            if (!$risk->excludedObjects->contains($relatedRisk->getObject())) {
                $relatedRisks[] = $relatedRisk->getObject()->getTitre();
            }
        }

        return $relatedRisks;
    }
}
