<?php
namespace HopitalNumerique\AutodiagBundle\Service;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Category;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Item as RestitutionItem;
use HopitalNumerique\AutodiagBundle\Model\Result\Item as ResultItem;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Model\Result\Score;
use \HopitalNumerique\AutodiagBundle\Service\Algorithm\Score as Algorithm;
use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderProvider;

class RestitutionCalculator
{
    /**
     * @var Algorithm
     */
    protected $algorithm;

    /**
     * @var AttributeBuilderProvider
     */
    protected $attributeBuilder;

    protected $items = [];
    protected $attributeResponses = [];


    /**
     * RestitutionCalculator constructor.
     * @param Algorithm $algorithm
     * @TODO Injecter un algorithm factory qui connaitrais tous les algo possible via un compileur pass
     */
    public function __construct(Algorithm $algorithm, AttributeBuilderProvider $attributeBuilder)
    {
        $this->algorithm = $algorithm;
        $this->attributeBuilder = $attributeBuilder;
    }

    public function compute(Synthesis $synthesis)
    {
        $this->computeCompletion($synthesis);

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
     * @param $references
     * @return ResultItem
     */
    protected function computeItemContainer(Container $container, Synthesis $synthesis, $references)
    {
        $cacheKey = $this->getCacheKey(
            $container->getAutodiag(),
            $container,
            $synthesis
        );

        if (!array_key_exists($cacheKey, $this->items)) {

            $score = $this->algorithm->getScore($synthesis, $container);

            $resultItem = new ResultItem();
            $resultItem->setLabel($container->getLabel());
            $resultItem->setScore(
                new Score($score)
            );

            $resultItem->setNumberOfQuestions($container->getTotalNumberOfAttributes());
            $resultItem->setNumberOfAnswers($this->countAnswers($synthesis, $container));

            foreach ($container->getChilds() as $child) {
                $resultItem->addChildren(
                    $this->computeItemContainer($child, $synthesis, $references)
                );
            }

            $this->items[$cacheKey] = $resultItem;
        }

        return $this->items[$cacheKey];
    }

    protected function computeCompletion(Synthesis $synthesis)
    {
        if (!array_key_exists($synthesis->getId(), $this->attributeResponses)) {
            $completion = [];
            $autodiag = $synthesis->getAutodiag();
            foreach ($autodiag->getAttributes() as $attribute) {
                /** @var Autodiag\Attribute $attribute */
                $completion[$attribute->getId()] = false;
                foreach ($synthesis->getEntries() as $entry) {
                    /** @var AutodiagEntry $entry */
                    foreach ($entry->getValues() as $value) {
                        /** @var AutodiagEntry\Value $value */
                        $builder = $this->attributeBuilder->getBuilder($attribute->getType());
                        if ($value->getAttribute()->getId() === $attribute->getId() && !$builder->isEmpty($value->getValue())) {
                            $completion[$attribute->getId()] = true;
                            break 2;
                        }
                    }
                }
            }

            $this->attributeResponses[$synthesis->getId()] = $completion;
        }
    }

    /**
     * Count number of container and container's childs  answers
     *
     * @param Synthesis $synthesis
     * @param Container $container
     * @return int
     */
    protected function countAnswers(Synthesis $synthesis, Container $container)
    {
        $answers = 0;
        foreach ($container->getAttributes() as $attribute) {
            $answers += $this->attributeResponses[$synthesis->getId()][$attribute->getId()] ? 1 : 0;
        }

        foreach ($container->getChilds() as $child) {
            $answers += $this->countAnswers($synthesis, $child);
        }

        return $answers;
    }

    protected function getCacheKey(Autodiag $autodiag, Container $container, Synthesis $synthesis)
    {
        return $autodiag->getId()
            . $container->getId()
            . $synthesis->getId();
    }
}
