<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service\Risk;

use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\RechercheParcoursBundle\DTO\RiskSynthesisDTO;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;
use HopitalNumerique\RechercheParcoursBundle\DTO\RiskSynthesisRiskDTO;
use HopitalNumerique\RechercheParcoursBundle\Service\GuidedSearchStepUrlGenerator;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\ObjectIdentityRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RiskSynthesisFactory
{
    /**
     * @var GuidedSearchStepUrlGenerator $guidedSearchUrlGenerator
     */
    protected $guidedSearchUrlGenerator;

    /**
     * @var ObjectIdentityRepository $objectIdentityRepository
     */
    protected $objectIdentityRepository;

    /**
     * RiskSynthesisFactory constructor.
     *
     * @param GuidedSearchStepUrlGenerator $guidedSearchUrlGenerator
     * @param ObjectIdentityRepository $objectIdentityRepository
     */
    public function __construct(GuidedSearchStepUrlGenerator $guidedSearchUrlGenerator, ObjectIdentityRepository $objectIdentityRepository)
    {
        $this->guidedSearchUrlGenerator = $guidedSearchUrlGenerator;
        $this->objectIdentityRepository = $objectIdentityRepository;
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


        foreach ($riskSynthesis->global->highestCriticalRisksAnalysis as $riskAnalysis) {
            if ($objects = $this->objectIdentityRepository->getRelatedByObjects(ObjectIdentity::createFromDomainObject($riskAnalysis->getRisk()), Objet::class)) {
                if (!isset($riskSynthesis->global->riskRelatedObjects[$riskAnalysis->getRisk()->getId()])) {
                    $riskSynthesis->global->riskRelatedObjects[$riskAnalysis->getRisk()->getId()] = [];
                }

                foreach ($objects as $object) {
                    foreach ($object->getObject()->getTypes() as $type) {
                        $riskSynthesis->global->riskRelatedObjects[$riskAnalysis->getRisk()->getId()][$type->getLibelle()] =
                            (isset($riskSynthesis->global->riskRelatedObjects[$riskAnalysis->getRisk()->getId()][$type->getLibelle()]) ?
                                $riskSynthesis->global->riskRelatedObjects[$riskAnalysis->getRisk()->getId()][$type->getLibelle()] : 0) + 1;
                    }
                }
            }
        }


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
                    $children = $parentReference->getReference()->getEnfants()->toArray();

                    usort($children, function (Reference $a, Reference $b) {
                        if ($a->getOrder() === $b->getOrder()) {
                            return 0;
                        }

                        return ($a->getOrder() < $b->getOrder()) ? -1 : 1;
                    });

                    foreach ($children as $subReference) {
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
