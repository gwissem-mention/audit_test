<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service\Risk;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\RechercheParcoursBundle\DTO\RiskSynthesisDTO;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;
use HopitalNumerique\RechercheParcoursBundle\DTO\RiskSynthesisRiskDTO;
use HopitalNumerique\RechercheParcoursBundle\Service\GuidedSearchStepUrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RiskSynthesisFactory
{
    /**
     * @var GuidedSearchStepUrlGenerator $guidedSearchUrlGenerator
     */
    protected $guidedSearchUrlGenerator;

    /**
     * RiskSynthesisFactory constructor.
     *
     * @param GuidedSearchStepUrlGenerator $guidedSearchUrlGenerator
     */
    public function __construct(GuidedSearchStepUrlGenerator $guidedSearchUrlGenerator)
    {
        $this->guidedSearchUrlGenerator = $guidedSearchUrlGenerator;
    }

    /**
     * Build risk synthesis DTO used displayed in risk synthesis page and PDF exprot
     *
     * @param GuidedSearch $guidedSearch
     * @param User|null $user
     *
     * @return RiskSynthesisDTO
     */
    public function buildRiskSynthesis(GuidedSearch $guidedSearch, User $user = null)
    {
        $riskSynthesis = new RiskSynthesisDTO();

        $riskSynthesis->global = RiskSynthesisRiskDTO::createFromGuidedSearchSteps($guidedSearch->getSteps(), $user);


        foreach ($guidedSearch->getGuidedSearchReference()->getRecherchesParcoursGestion()->getRechercheParcours() as $guidedSearchReference) {
            if ($guidedSearchReference !== $guidedSearch->getGuidedSearchReference()) {
                continue;
            }

            foreach ($guidedSearchReference->getRecherchesParcoursDetails() as $parentReference) {

                $steps = $guidedSearch->getSteps()->filter(function (GuidedSearchStep $guidedSearchStep) use ($parentReference) {
                    list($parentReferenceId) = explode(':', $guidedSearchStep->getStepPath());

                    return $parentReference->getId() == $parentReferenceId;
                });

                $riskSynthesis->parents[$parentReference->getReference()->getLibelle()] = RiskSynthesisRiskDTO::createFromGuidedSearchSteps($steps, $user);

                $riskSynthesis->parents[$parentReference->getReference()->getLibelle()]->directLink = $this->guidedSearchUrlGenerator->generateFromGuidedSearchAndStepPath($guidedSearch, $parentReference->getId(), UrlGeneratorInterface::ABSOLUTE_URL);

                if ($parentReference->getShowChildren() && $parentReference->getReference()->getEnfants()->count() > 0) {
                    foreach ($parentReference->getReference()->getEnfants() as $subReference) {
                        $steps = $guidedSearch->getSteps()->filter(function (GuidedSearchStep $guidedSearchStep) use ($parentReference, $subReference) {
                            $stepPath = explode(':', $guidedSearchStep->getStepPath());
                            if (count($stepPath) < 2) {
                                return false;
                            }

                            list($parentReferenceId, $subReferenceId) = $stepPath;

                            return $parentReference->getId() == $parentReferenceId && $subReference->getId() == $subReferenceId;
                        });

                        $riskSynthesis->subReferences[$parentReference->getReference()->getLibelle()][$subReference->getLibelle()] = RiskSynthesisRiskDTO::createFromGuidedSearchSteps($steps, $user);

                        $riskSynthesis->subReferences[$parentReference->getReference()->getLibelle()][$subReference->getLibelle()]->directLink = $this->guidedSearchUrlGenerator->generateFromGuidedSearchAndStepPath($guidedSearch, $parentReference->getId() . ':' . $subReference->getId());
                    }
                }
            }
        }

        return $riskSynthesis;
    }
}
