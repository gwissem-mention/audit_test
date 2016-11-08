<?php

namespace HopitalNumerique\AutodiagBundle\Service\Score;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Score;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Repository\ScoreRepository;

class BoundaryCalculator
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var ScoreRepository */
    protected $scoreRepository;

    public function __construct(EntityManager $entityManager, ScoreRepository $scoreRepository)
    {
        $this->entityManager = $entityManager;
        $this->scoreRepository = $scoreRepository;
    }

    public function computeBoundaries(Synthesis $synthesis, $source)
    {
        $sourceIds = array_map(function (Synthesis $synthesis) {
            return $synthesis->getId();
        }, $source);

        $autodiag = $synthesis->getAutodiag();

        foreach ($autodiag->getContainers() as $container) {
            $boundaries = $this->scoreRepository->getBoundariesFromSyntheses($container, $sourceIds);

            $score = $this->scoreRepository->findOneBy([
                'synthesis' => $synthesis,
                'container' => $container,
            ]);

            if (null === $score) {
                $score = new Score($container, $synthesis);
                $this->entityManager->persist($score);
            }

            $score->setMin($boundaries['min_score']);
            $score->setMax($boundaries['max_score']);
        }
    }
}
