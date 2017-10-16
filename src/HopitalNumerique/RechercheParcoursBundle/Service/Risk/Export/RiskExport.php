<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service\Risk\Export;

use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\ObjetBundle\Entity\RelatedRisk;
use HopitalNumerique\RechercheParcoursBundle\DTO\StepRiskDTO;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class RiskExport
{
    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * @var RouterInterface $router
     */
    protected $router;

    /**
     * @var CurrentDomaine $currentDomainService
     */
    protected $currentDomainService;

    /**
     * RiskExport constructor.
     *
     * @param TranslatorInterface $translator
     * @param RouterInterface $router
     * @param CurrentDomaine $currentDomainService
     */
    public function __construct(TranslatorInterface $translator, RouterInterface $router, CurrentDomaine $currentDomainService)
    {
        $this->translator = $translator;
        $this->router = $router;
        $this->currentDomainService = $currentDomainService;
    }

    /**
     * @param GuidedSearchStep $guidedSearchStep
     * @param StepRiskDTO[] $risks
     *
     * @return string Filepath
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
        $currentDomain = $this->currentDomainService->get();
        $relatedRisks = [];
        /** @var RelatedRisk $relatedRisk */
        foreach ($risk->relatedRisks as $relatedRisk) {
            if (
                !$risk->excludedObjects->contains($relatedRisk->getObject()) &&
                $relatedRisk->getObject()->getDomaines()->contains($currentDomain)
            ) {
                $relatedRisks[] = sprintf(
                    '%s (%s)',
                    $relatedRisk->getObject()->getObjectIdentityTitle(),
                    $this->router->generate('hopital_numerique_publication_publication_objet', ['id' => $relatedRisk->getObject()->getId()], RouterInterface::ABSOLUTE_URL)
                );
            }
        }

        return $relatedRisks;
    }

    /**
     * @return string
     */
    protected function getFilePath()
    {
        return stream_get_meta_data(tmpfile())['uri'];
    }
}
