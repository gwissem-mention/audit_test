<?php

namespace HopitalNumerique\AutodiagBundle\Service\Score;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Score;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Repository\AutodiagEntry\ValueRepository;
use HopitalNumerique\AutodiagBundle\Repository\ScoreRepository;
use HopitalNumerique\AutodiagBundle\Service\Algorithm\AlgorithmInterface;

class BoundaryCalculator
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var ScoreRepository */
    protected $scoreRepository;

    /** @var ValueRepository */
    protected $valueRepository;

    /** @var AlgorithmInterface */
    protected $algorithm;

    public function __construct(EntityManager $entityManager, ScoreRepository $scoreRepository, ValueRepository $valueRepository, AlgorithmInterface $algorithm)
    {
        $this->entityManager = $entityManager;
        $this->scoreRepository = $scoreRepository;
        $this->valueRepository = $valueRepository;
        $this->algorithm = $algorithm;
    }

    /**
     * Guess boundaries from source syntheses.
     *
     * @param Synthesis $synthesis
     * @param $source
     */
    public function guessBoundaries(Synthesis $synthesis, $source)
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

    /**
     * Compute synthesis boundaries from there entries.
     *
     * @param Synthesis $synthesis
     */
    public function computeBoundaries(Synthesis $synthesis)
    {
        if (null === $synthesis->getId()) {
            throw new \LogicException('Synthesis must be persisted before computing boundaries');
        }

        $autodiag = $synthesis->getAutodiag();
        $entriesValues = [];
        $autodiagEntries = [];

        // Get values for algorithm by entry
        foreach ($synthesis->getEntries() as $entry) {
            $entriesValues[$entry->getId()] = $this->valueRepository->getSynthesisEntryValuesForAlgorithm(
                $synthesis,
                $entry
            );
            $autodiagEntries[$entry->getId()] = $entry;
        }

        // find min and max for each autodiag containers
        foreach ($autodiag->getContainers() as $container) {
            $scores = [];
            foreach ($entriesValues as $entryId => $entryValues) {
                $scores[$entryId] = $this->algorithm->getScore($container, $entryValues);
            }

            $minAutodiagEntry = $maxAutodiagEntry = current($autodiagEntries);
            if (empty($scores)) {
                $min = $max = null;
            } elseif (count($scores) === 1) {
                $min = $max = current($scores);
            } else {
                $min = call_user_func_array('min', $scores);
                $max = call_user_func_array('max', $scores);

                $minAutodiagEntry = $autodiagEntries[array_search($min, $scores)];
                $maxAutodiagEntry = $autodiagEntries[array_search($max, $scores)];
            }

            if (null !== $min || null !== $max) {
                $score = $this->scoreRepository->findOneBy([
                    'synthesis' => $synthesis,
                    'container' => $container,
                ]);

                if (null === $score) {
                    $score = new Score($container, $synthesis);
                    $this->entityManager->persist($score);
                }

                $score
                    ->setMin($min)
                    ->setMax($max)
                    ->setMinAutodiagEntry($minAutodiagEntry)
                    ->setMaxAutodiagEntry($maxAutodiagEntry)
                ;
            }
        }

        $this->entityManager->flush();
    }
}
