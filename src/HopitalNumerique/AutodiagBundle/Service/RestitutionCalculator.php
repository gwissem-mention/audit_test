<?php
namespace HopitalNumerique\AutodiagBundle\Service;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Category;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Item as RestitutionItem;
use HopitalNumerique\AutodiagBundle\Model\Result\Item as ResultItem;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Model\Result\Score;

class RestitutionCalculator
{


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

        return $result;
    }

    protected function computeItem(RestitutionItem $item, Synthesis $synthesis)
    {
        $result = [];
        $containers = $item->getContainers();
        foreach ($containers as $container) {
            /** @var Container $container */
            $result[] = $this->computeItemContainer($container, $synthesis);
        }

        return $result;
    }

    protected function computeItemContainer(Container $container, $synthesis)
    {
        $resultItem = new ResultItem();
        $resultItem->setLabel($container->getLabel());
        $resultItem->setScore(
            new Score(rand(0, 100))
        );
        $resultItem->addReference(
            new Score(rand(0, 100), 'Référene 1', 1)
        );
        $resultItem->addReference(
            new Score(rand(0, 100), 'Référene 1', 1)
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

        return $resultItem;
    }
}
