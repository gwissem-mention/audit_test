<?php

namespace HopitalNumerique\ObjetBundle\Service\Risk;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\ObjectIdentityRepository;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\RelationRepository;
use HopitalNumerique\ObjetBundle\Entity\Risk;
use HopitalNumerique\ObjetBundle\Entity\RelatedRisk;
use HopitalNumerique\RechercheParcoursBundle\Entity\RiskAnalysis;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use HopitalNumerique\RechercheParcoursBundle\Repository\RiskAnalysisRepository;

class Fusion
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var RiskAnalysisRepository $riskAnalysisRepository
     */
    protected $riskAnalysisRepository;

    /**
     * @var ObjectIdentityRepository $objectIdentityRepository
     */
    protected $objectIdentityRepository;

    /**
     * @var RelationRepository $relationRepository
     */
    protected $relationRepository;

    /**
     * Fusion constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RiskAnalysisRepository $riskAnalysisRepository
     * @param ObjectIdentityRepository $objectIdentityRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RiskAnalysisRepository $riskAnalysisRepository,
        ObjectIdentityRepository $objectIdentityRepository,
        RelationRepository $relationRepository
    ) {
        $this->entityManager = $entityManager;
        $this->riskAnalysisRepository = $riskAnalysisRepository;
        $this->objectIdentityRepository = $objectIdentityRepository;
        $this->relationRepository = $relationRepository;
    }

    /**
     * Merge $risk in $target. Related productions and risk analysis too.
     *
     * @param Risk $risk
     * @param Risk $target
     *
     * @return Risk
     */
    public function fusion(Risk $risk, Risk $target)
    {
        if ($risk === $target) {
            throw new BadRequestHttpException();
        }

        foreach ($risk->getDomains() as $domain) {
            $target->addDomain($domain);
        }

        foreach ($this->relationRepository->getObjectIdentityRelatedByRelations(ObjectIdentity::createFromDomainObject($risk)) as $relation) {
            $this->relationRepository->addRelation($relation->getSourceObjectIdentity(), ObjectIdentity::createFromDomainObject($target));

            $this->entityManager->remove($relation);
        }

        $riskAnalysis = $this->riskAnalysisRepository->getRiskAnalysisForRisk($risk);
        $targetRiskAnalysis = new ArrayCollection($this->riskAnalysisRepository->getRiskAnalysisForRisk($target));

        foreach ($riskAnalysis as $riskAnalyse) {
            $hasTargetRiskAnalyse = $targetRiskAnalysis->filter(function (RiskAnalysis $targetRiskAnalyse) use ($riskAnalyse) {
                return $targetRiskAnalyse->getStep() === $riskAnalyse->getStep() && $targetRiskAnalyse->getOwner() === $riskAnalyse->getOwner();
            })->count() > 0;

            if (!$hasTargetRiskAnalyse) {
                $riskAnalyse->setRisk($target);
            } else {
                $this->entityManager->remove($riskAnalyse);
            }
        }

        $this->entityManager->remove($risk);

        return $target;
    }
}
