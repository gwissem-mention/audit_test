<?php

namespace HopitalNumerique\AutodiagBundle\Service\Score;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Repository\AutodiagEntry\ValueRepository;
use HopitalNumerique\AutodiagBundle\Service\Algorithm\Score;
use HopitalNumerique\AutodiagBundle\Service\Synthesis\Completion;

class ScoreCalculator
{
    /**
     * @var Score
     */
    protected $algorithm;

    /**
     * @var ValueRepository
     */
    protected $valueRepository;

    /**
     * @var EntityRepository
     */
    protected $scoreRepository;

    /**
     * @var Completion
     */
    protected $completion;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(
        Score $algorithm,
        ValueRepository $valueRepository,
        EntityRepository $scoreRepository,
        Completion $completion,
        EntityManager $entityManager
    ) {
        $this->algorithm = $algorithm;
        $this->valueRepository = $valueRepository;
        $this->scoreRepository = $scoreRepository;
        $this->completion = $completion;
        $this->entityManager = $entityManager;
    }

    public function computeSynthesisScore(Synthesis $synthesis, $flush = true)
    {
        $autodiag = $synthesis->getAutodiag();
        $containers = $autodiag->getContainers();

        $values = $this->valueRepository->getSynthesisValuesForAlgorithm($synthesis);

        foreach ($containers as $container) {
            foreach ($this->computeScoreRecursive($synthesis, $container, $values) as $score) {
                $existingScore = $this->scoreRepository->find([
                    'container' => $score->getContainer(),
                    'synthesis' => $score->getSynthesis()
                ]);

                if (null === $existingScore) {
                    $existingScore = $score;
                    $this->entityManager->persist($existingScore);
                }
                $existingScore->setScore($score->getScore());
                $existingScore->setComplete($this->completion->isComplete($synthesis, $container));
            }
        }

        $synthesis->setCompletion(
            $this->completion->getCompletionRate($synthesis)
        );

        if ($flush) {
//            $this->entityManager->flush();
        }
    }

    /**
     * @param AutodiagEntry $entry
     * @param Container[] $containers
     */
    public function computeEntryScoreForContainers(AutodiagEntry $entry, $containers)
    {
        $syntheses = $entry->getSyntheses();

        foreach ($syntheses as $synthesis) {
            $values = $this->valueRepository->getSynthesisValuesForAlgorithm($synthesis);

            foreach ($containers as $container) {
                foreach ($this->computeScoreRecursive($synthesis, $container, $values) as $score) {
                    $existingScore = $this->scoreRepository->find([
                        'container' => $score->getContainer(),
                        'synthesis' => $score->getSynthesis()
                    ]);

                    if (null === $existingScore) {
                        $existingScore = $score;
                        $this->entityManager->persist($existingScore);
                    }
                    $existingScore->setScore($score->getScore());
                    $existingScore->setComplete($this->completion->isComplete($synthesis, $container));
                }
            }

            $synthesis->setCompletion(
                $this->completion->getCompletionRate($synthesis)
            );
        }

//        $this->entityManager->flush();
    }

    protected function computeScoreRecursive(Synthesis $synthesis, Container $container, $values)
    {
        $score = $this->algorithm->getScore($container, $values);
        yield new \HopitalNumerique\AutodiagBundle\Entity\Score($container, $synthesis, $score);

        $parent = $container->getParent();
        while (null !== $parent) {
            $subScores = $this->computeScoreRecursive($synthesis, $parent, $values);
            foreach ($subScores as $score) {
                yield $score;
            }
            $parent = $parent->getParent();
        }
    }
}
