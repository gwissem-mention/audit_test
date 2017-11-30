<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service;

use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ObjetBundle\Entity\Risk;
use HopitalNumerique\ObjetBundle\Repository\RiskRepository;
use HopitalNumerique\RechercheParcoursBundle\DTO\StepRiskDTO;
use HopitalNumerique\RechercheParcoursBundle\Entity\RiskAnalysis;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\ObjectIdentityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StepRisksFactory
{
    /**
     * @var RiskRepository $riskRepository
     */
    protected $riskRepository;

    /**
     * @var TokenStorageInterface $tokenStorage
     */
    protected $tokenStorage;

    /**
     * @var ObjectIdentityRepository $objectIdentityRepository
     */
    protected $objectIdentityRepository;

    /**
     * StepRisksFactory constructor.
     *
     * @param RiskRepository $riskRepository
     * @param TokenStorageInterface $tokenStorage
     * @param ObjectIdentityRepository $objectIdentityRepository
     */
    public function __construct(RiskRepository $riskRepository, TokenStorageInterface $tokenStorage, ObjectIdentityRepository $objectIdentityRepository)
    {
        $this->riskRepository = $riskRepository;
        $this->tokenStorage = $tokenStorage;
        $this->objectIdentityRepository = $objectIdentityRepository;
    }


    /**
     * @param Domaine $domain
     * @param GuidedSearch $guidedSearch
     * @param GuidedSearchStep|null $guidedSearchStep
     *
     * @return array|StepRiskDTO[]
     */
    public function getStepRiskDTO(Domaine $domain, GuidedSearch $guidedSearch, GuidedSearchStep $guidedSearchStep = null)
    {
        $risks = $this
            ->riskRepository
            ->getPublicRisksForDomain(
                $domain,
                $guidedSearchStep ?
                    $guidedSearchStep->getReferencesId() :
                    null
            )
        ;

        $availableRisks = $this
            ->riskRepository
            ->getRisksForDomainAndReference(
                $domain,
                $guidedSearchStep ?
                    $guidedSearchStep->getReferencesId() :
                    null
            )
        ;

        foreach ($guidedSearch->getPrivateRisks() as $privateRisk) {
            if (!isset($availableRisks[$privateRisk->getId()])) {
                continue;
            }

            $risks[$privateRisk->getId()] = $privateRisk;
        }

        if (!is_null($guidedSearchStep)) {
            foreach ($guidedSearchStep->getExcludedRisks() as $excludedRisk) {
                if (isset($risks[$excludedRisk->getId()])) {
                    unset($risks[$excludedRisk->getId()]);
                }
            }
        }

        if (!$guidedSearchStep->isAnalyzed()) {
            usort($risks, function (Risk $a, Risk $b) {
                if ($a->getNature()->getOrder() !== $b->getNature()->getOrder()) {
                    return $a->getNature()->getOrder() > $b->getNature()->getOrder();
                }

                return strcasecmp($a->getLabel(), $b->getLabel());
            });
        }

        $risks = $this->risksToDTO($risks, $guidedSearchStep);

        if ($guidedSearchStep->isAnalyzed()) {
            usort($risks, function (StepRiskDTO $a, StepRiskDTO $b) {
                $aCriticality = $a->impact * $a->probability;
                $bCriticality = $b->impact * $b->probability;

                if ($aCriticality === $bCriticality) {
                    return 0;
                }

                return $aCriticality > $bCriticality ? -1 : 1;
            });
        }

        return $risks;
    }

    /**
     * @param array|Risk[] $risks
     * @param GuidedSearchStep|null $guidedSearchStep
     *
     * @return array|StepRiskDTO[]
     */
    private function risksToDTO($risks, GuidedSearchStep $guidedSearchStep = null)
    {
        $risksDTO = [];
        /** @var Risk $risk */
        foreach ($risks as $risk) {
            $riskDTO = new StepRiskDTO();
            $riskDTO->riskId = $risk->getId();
            $riskDTO->label = $risk->getLabel();
            $riskDTO->natureLabel = $risk->getNature()->getLibelle();
            $riskDTO->natureCode = $risk->getNature()->getSigle();
            $riskDTO->relatedRisks = $this->objectIdentityRepository->getRelatedByObjects(ObjectIdentity::createFromDomainObject($risk));


            if (is_null($guidedSearchStep)) {
                $risksDTO[] = $riskDTO;

                continue;
            }

            /** @var RiskAnalysis $riskAnalysis */
            $riskAnalysis = $guidedSearchStep->getRisksAnalysis()->filter(function (RiskAnalysis $riskAnalysis) use($risk) {
                return $riskAnalysis->getRisk() === $risk && (
                        is_null($riskAnalysis->getOwner()) ||
                        $riskAnalysis->getOwner() === $this->tokenStorage->getToken()->getUser()
                );
            })->first();

            if ($riskAnalysis) {
                $riskDTO->probability = $riskAnalysis->getProbability();
                $riskDTO->impact = $riskAnalysis->getImpact();
                $riskDTO->initialSkillsRate = $riskAnalysis->getInitialSkillsRate();
                $riskDTO->currentSkillsRate = $riskAnalysis->getCurrentSkillsRate();
                $riskDTO->comment = $riskAnalysis->getComment();
                $riskDTO->excludedObjects = $riskAnalysis->getExcludedObjects();
            }

            $risksDTO[] = $riskDTO;
        }

        return $risksDTO;
    }
}
