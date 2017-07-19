<?php

namespace HopitalNumerique\ObjetBundle\Service\Risk;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
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
     * Fusion constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RiskAnalysisRepository $riskAnalysisRepository
     */
    public function __construct(EntityManagerInterface $entityManager, RiskAnalysisRepository $riskAnalysisRepository)
    {
        $this->entityManager = $entityManager;
        $this->riskAnalysisRepository = $riskAnalysisRepository;
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

        foreach ($risk->getRelatedRisks() as $relatedRisk) {
            if ($target->getRelatedRisks()->filter(function (RelatedRisk $targetRelatedRisk) use ($relatedRisk) {
                    return $targetRelatedRisk->getObject() === $relatedRisk->getObject();
                })->count() === 0) {
                $newRelatedRisk = new RelatedRisk($relatedRisk->getObject(), $target, $relatedRisk->getPosition());
                $this->entityManager->persist($newRelatedRisk);
            }
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
