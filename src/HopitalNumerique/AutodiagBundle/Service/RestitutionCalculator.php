<?php
namespace HopitalNumerique\AutodiagBundle\Service;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Category;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Item as RestitutionItem;
use HopitalNumerique\AutodiagBundle\Model\Result\Item as ResultItem;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Model\Result\Score;

class RestitutionCalculator
{

    protected $items = [];

    public function compute(Synthesis $synthesis)
    {
        $autodiag = $synthesis->getAutodiag();
        $restitution = $autodiag->getRestitution();

        $result = [];

        foreach ($restitution->getCategories() as $category) {
            /** @var Category $category */
            foreach ($category->getItems() as $item) {
                /** @var RestitutionItem $item */
                $result[$item->getId()] = $this->computeItem($item, $synthesis);
            }
        }
dump($result);die;
        return $result;
    }

    protected function computeItem(RestitutionItem $item, Synthesis $synthesis)
    {
        $result = [
            'items' => [],
            'references' => [],
        ];

        $containers = $item->getContainers();
        foreach ($containers as $container) {
            /** @var Container $container */
            $resultItem = $this->computeItemContainer($container, $synthesis, $item->getReferences());

            foreach ($item->getReferences() as $reference) {
                $resultItem->addReference(
                    new Score(rand(0, 100), $reference->getLabel(), $reference->getId())
                );

                $result['references'][$reference->getId()] = $reference->getLabel();
            }

            $result['items'][] = $resultItem;
        }

        return $result;
    }

    /**
     * @param Container $container
     * @param Synthesis $synthesis
     * @return ResultItem
     */
    protected function computeItemContainer(Container $container, Synthesis $synthesis, $references)
    {
        $cacheKey = $this->getCacheKey(
            $container->getAutodiag(),
            $container,
            $synthesis
        );

        $algorithm = new \HopitalNumerique\AutodiagBundle\Service\Algorithm\Score();

        if (!array_key_exists($cacheKey, $this->items)) {

            $score = $algorithm->getScore($synthesis, $container);

            $resultItem = new ResultItem();
            $resultItem->setLabel($container->getLabel());
            $resultItem->setScore(
                new Score(rand(0, 100))
            );
            $resultItem->setNumberOfQuestions(rand(1, 50));
            $resultItem->setNumberOfAnswers(
                floor((rand(0, 100)/100) * $resultItem->getNumberOfQuestions())
            );

            foreach ($container->getChilds() as $child) {
                $resultItem->addChildren(
                    $this->computeItemContainer($child, $synthesis)
                );
            }

            $this->items[$cacheKey] = $resultItem;
        }

        return $this->items[$cacheKey];
    }

    protected function getCacheKey(Autodiag $autodiag, Container $container, Synthesis $synthesis)
    {
        return $autodiag->getId()
            . $container->getId()
            . $synthesis->getId();
    }
}
