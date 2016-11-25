<?php

namespace HopitalNumerique\AutodiagBundle\Service\Compare;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Compare;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Repository\CompareRepository;
use HopitalNumerique\AutodiagBundle\Service\Score\ScoreCalculator;
use HopitalNumerique\AutodiagBundle\Service\Synthesis\IntersectionBuilder;
use HopitalNumerique\UserBundle\Entity\User;

class ComparisonBuilder
{
    /** @var IntersectionBuilder */
    protected $intersectionBuilder;

    /** @var ScoreCalculator */
    protected $scoreCalculator;

    /** @var CompareRepository */
    protected $compareRepository;

    /** @var EntityManager */
    protected $entityManager;

    /**
     * ComparisonBuilder constructor.
     * @param IntersectionBuilder $intersectionBuilder
     * @param ScoreCalculator $scoreCalculator
     * @param EntityManager $entityManager
     */
    public function __construct(IntersectionBuilder $intersectionBuilder, ScoreCalculator $scoreCalculator, CompareRepository $compareRepository, EntityManager $entityManager)
    {
        $this->intersectionBuilder = $intersectionBuilder;
        $this->scoreCalculator = $scoreCalculator;
        $this->compareRepository = $compareRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Build a new comparison. Compute intersection between synthesis and reference, persist them, compute there score
     * and save the comparison
     *
     * @param Synthesis $synthesis
     * @param Synthesis $reference
     * @return Compare
     */
    public function build(User $user, Synthesis $synthesis, Synthesis $reference)
    {
        $compare = $this->compareRepository->findFromOrigin($synthesis, $reference);

        if ($compare instanceof Compare) {
            return $compare;
        }

        $synthesisCopy = $this->intersectionBuilder->build($user, $synthesis, $reference);
        $referenceCopy = $this->intersectionBuilder->build($user, $reference, $synthesis);

        // Must persist copies to compute there scores
        $this->entityManager->persist($synthesisCopy);
        $this->entityManager->persist($referenceCopy);
        $this->entityManager->flush();

        $this->scoreCalculator->computeSynthesisScore($synthesisCopy);
        $this->scoreCalculator->computeSynthesisScore($referenceCopy);

        $compare = new Compare($synthesisCopy, $referenceCopy);

        return $compare;
    }
}
