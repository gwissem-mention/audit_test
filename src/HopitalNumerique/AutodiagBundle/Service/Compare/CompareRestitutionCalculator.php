<?php

namespace HopitalNumerique\AutodiagBundle\Service\Compare;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\Compare;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Category;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Item;
use HopitalNumerique\AutodiagBundle\Model\Result\ComparedItem;
use HopitalNumerique\AutodiagBundle\Model\Result\ComparedItemResponse;
use HopitalNumerique\AutodiagBundle\Model\Result\ComparedScore;
use HopitalNumerique\AutodiagBundle\Model\Result\ItemResponse;
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
     * @Todo : find better solution than this callback. Class RestitutionCalculator should be exploded as reusable services
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
            $comparedScore->getValue() - $referenceScore->getValue()
        );

        $item->setScore($comparedScore);

        foreach ($this->restitutionCalculator->getResponses($compare->getReference(), $container) as $response) {
            foreach ($item->getAttributes() as $itemAttribute) {
                if ($itemAttribute->attributeId == $response['attribute_id']) {
                    $itemResponse = new ComparedItemResponse(
                        $itemAttribute->response->getValue(),
                        $itemAttribute->response->getText(),
                        $itemAttribute->response->getComment(),
                        $itemAttribute->response->getScore()
                    );

                    if (null !== $itemAttribute->response->getActionPlan()) {
                        $itemResponse->setActionPlan($itemAttribute->response->getActionPlan());
                    }

                    $tempResultItem = new ResultItem();
                    $this->restitutionCalculator->computeResultItemAttribute($tempResultItem, $compare->getReference(), $response);
                    $itemResponse->setReference($tempResultItem->getAttributes()[0]->response);

                    $itemAttribute->response = $itemResponse;
                }
            }
        }

        return $item;
    }
}
