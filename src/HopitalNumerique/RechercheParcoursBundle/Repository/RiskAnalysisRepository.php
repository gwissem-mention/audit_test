<?php

namespace HopitalNumerique\RechercheParcoursBundle\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityRepository;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ObjetBundle\Entity\Risk;
use HopitalNumerique\RechercheParcoursBundle\Entity\RiskAnalysis;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;

/**
 * RiskAnalysisRepository.
 */
class RiskAnalysisRepository extends EntityRepository
{
    /**
     * @param GuidedSearchStep $guidedSearchStep
     * @param Risk $risk
     * @param User|null $user
     *
     * @return RiskAnalysis|null
     */
    public function findOneByStepAndRisk(GuidedSearchStep $guidedSearchStep, Risk $risk, User $user = null)
    {
        $queryBuilder = $this->createQueryBuilder('ra')
            ->join('ra.step', 's', Join::WITH, 's.id = :stepId')
            ->setParameter('stepId', $guidedSearchStep->getId())
            ->join('ra.risk', 'r', Join::WITH, 'r.id = :riskId')
            ->setParameter('riskId', $risk->getId())
        ;

        if (is_null($user)) {
            $queryBuilder->andWhere('ra.owner IS NULL');
        } else {
            $queryBuilder
                ->join('ra.owner', 'o', Join::WITH, 'o.id = :ownerId')
                ->setParameter('ownerId', $user->getId())
            ;
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Risk $risk
     *
     * @return RiskAnalysis[]
     */
    public function getRiskAnalysisForRisk(Risk $risk)
    {
        return $this->createQueryBuilder('ra')
            ->join('ra.step', 's')->addSelect('s')
            ->leftJoin('ra.owner', 'o')->addSelect('o')

            ->andWhere('ra.risk = :riskId')->setParameter('riskId', $risk->getId())

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param GuidedSearchStep $guidedSearchStep
     * @param Risk $risk
     * @param User|null $user
     *
     * @return RiskAnalysis|mixed
     */
    public function getOrCreate(GuidedSearchStep $guidedSearchStep, Risk $risk, User $user = null)
    {
        $riskAnalysis = $this->findOneByStepAndRisk($guidedSearchStep, $risk, $user);

        if (is_null($riskAnalysis)) {
            $riskAnalysis = new RiskAnalysis();
            $riskAnalysis
                ->setRisk($risk)
                ->setOwner($user)
                ->setStep($guidedSearchStep)
            ;

            $this->_em->persist($riskAnalysis);
        }

        return $riskAnalysis;
    }

    /**
     * @param GuidedSearchStep $step
     * @param User             $user
     *
     * @return RiskAnalysis[]
     */
    public function findByStepAndUser(GuidedSearchStep $step, User $user)
    {
        return $this->createQueryBuilder('riskAnalysis')
            ->join('riskAnalysis.step', 'step', Join::WITH, 'step.id = :stepId')
            ->setParameter('stepId', $step->getId())
            ->join('riskAnalysis.owner', 'owner', Join::WITH, 'owner.id = :ownerId')
            ->setParameter('ownerId', $user->getId())
            ->andWhere('riskAnalysis.probability IS NOT NULL')
            ->andWhere('riskAnalysis.impact IS NOT NULL')
            ->getQuery()
            ->getResult()
        ;
    }
}
