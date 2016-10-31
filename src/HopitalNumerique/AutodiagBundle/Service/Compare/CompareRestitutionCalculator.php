<?php

namespace HopitalNumerique\AutodiagBundle\Service\Compare;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\Compare;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Category;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Item;
use HopitalNumerique\AutodiagBundle\Model\Result\ComparedItem;
use HopitalNumerique\AutodiagBundle\Model\Result\ComparedScore;
use HopitalNumerique\AutodiagBundle\Model\Result\Score;
use HopitalNumerique\AutodiagBundle\Repository\RestitutionRepository;
use HopitalNumerique\AutodiagBundle\Service\RestitutionCalculator;
use \HopitalNumerique\AutodiagBundle\Model\Result\Item as ResultItem;

class CompareRestitutionCalculator
{
    /** @var RestitutionCalculator */
    protected $restitutionCalculator;

    /** @var RestitutionRepository */
    protected $restitutionRepository;

    /**
     * CompareRestitutionCalculator constructor.
     * @param $restitutionCalculator
     */
    public function __construct(RestitutionCalculator $restitutionCalculator, RestitutionRepository $restitutionRepository)
    {
        $this->restitutionCalculator = $restitutionCalculator;
        $this->restitutionRepository = $restitutionRepository;
    }

    public function compute(Compare $compare)
    {

        $this->restitutionCalculator->setResultItemCreatedCallback(function ($item, $container) use ($compare) {
            return $this->updateResultScore($item, $container, $compare);
        });

        $autodiag = $compare->getSynthesis()->getAutodiag();
        $restitution = $this->restitutionRepository->getForAutodiag($autodiag);

        $result = [];

        foreach ($restitution->getCategories() as $category) {
            /** @var Category $category */
            foreach ($category->getItems() as $item) {
                /** @var Item $item */
                $result[$item->getId()] = $this->restitutionCalculator->computeItem($item, $compare->getSynthesis());
            }
        }

        return $result;
    }

    /**
     * Alter original computed score to add reference score
     *
     * @param ResultItem $item
     * @param Container $container
     * @param Compare $compare
     * @return ResultItem
     */
    protected function updateResultScore(ResultItem $item, Container $container, Compare $compare)
    {
        $score = $item->getScore();
        $comparedScore = new ComparedScore(
            $score->getValue(),
            $score->getLabel(),
            $score->getCode(),
            $score->getColor()
        );

        $referenceScore = new Score(
            $this->restitutionCalculator->getContainerSynthesisScore($compare->getReference(), $container->getId()),
            $compare->getReference()->getName()
        );

        $comparedScore->setReference(
            $referenceScore
        );

        $comparedScore->setVariation(
            $referenceScore->getValue() * 100 / $comparedScore->getValue()
        );

        $item->setScore($comparedScore);

        return $item;
    }
}
