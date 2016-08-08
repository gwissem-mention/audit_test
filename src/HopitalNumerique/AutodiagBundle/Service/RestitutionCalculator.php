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
use HopitalNumerique\AutodiagBundle\Repository\AutodiagEntryRepository;
use HopitalNumerique\AutodiagBundle\Service\Algorithm\Reference\Average;
use HopitalNumerique\AutodiagBundle\Service\Algorithm\ReferenceAlgorithm;
use \HopitalNumerique\AutodiagBundle\Service\Algorithm\Score as Algorithm;
use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderProvider;
use HopitalNumerique\AutodiagBundle\Service\Synthesis\Completion;

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

    /**
     * @var AutodiagEntryRepository
     */
    protected $entryRepository;

    /**
     * @var Completion
     */
    protected $completion;

    protected $items = [];
    protected $references = [];
    protected $attributeResponses = [];


    /**
     * RestitutionCalculator constructor.
     * @param Algorithm $algorithm
     * @TODO Injecter un algorithm factory qui connaitrais tous les algo possible via un compileur pass
     */
    public function __construct(
        Algorithm $algorithm,
        AttributeBuilderProvider $attributeBuilder,
        AutodiagEntryRepository $entryRepository,
        Completion $completion
    ) {
        $this->algorithm = $algorithm;
        $this->attributeBuilder = $attributeBuilder;
        $this->entryRepository = $entryRepository;
        $this->completion = $completion;
    }

    public function compute(Synthesis $synthesis)
    {
        $this->attributeResponses[$synthesis->getId()] = $this->completion->getGlobalCompletion($synthesis);

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

            $score = $this->algorithm->getScore(
                $synthesis->getAutodiag(),
                $container,
                $synthesis->getEntries()->toArray()
            );

            $resultItem = new ResultItem();
            $resultItem->setLabel($container->getLabel());
            $resultItem->setScore(
                new Score($score)
            );

            $resultItem->setNumberOfQuestions($container->getTotalNumberOfAttributes());
            $resultItem->setNumberOfAnswers($this->countAnswers($synthesis, $container));

            if (count($references) > 0) {
                foreach ($references as $reference) {
                    $referenceScore = $this->getReferenceScore($reference, $container);
                    if ($referenceScore instanceof Score) {
                        $resultItem->addReference($referenceScore);
                    }
                }
            }

            foreach ($container->getChilds() as $child) {
                $resultItem->addChildren(
                    $this->computeItemContainer($child, $synthesis, $references)
                );
            }

            $this->items[$cacheKey] = $resultItem;
        }

        return $this->items[$cacheKey];
    }

    protected function getReferenceScore(Autodiag\Reference $reference, Container $container)
    {
        $cacheKey = implode('-', [
            $reference->getValue(),
            $container->getCode(),
        ]);

        if (!array_key_exists($cacheKey, $this->references)) {
            $autodiag = $container->getAutodiag();
            $referenceEntries = $this->getCompleteEntriesForContainer($container);
            $referenceScores = [];
            foreach ($referenceEntries as $entry) {
                $referenceScores[] = $this->algorithm->getScore($autodiag, $container, [$entry]);
            }

            $score = null;
            if (count($referenceScores) > 0) {
                $score = new Score(
                    ReferenceAlgorithm::compute($reference, $referenceScores),
                    $reference->getLabel(),
                    $reference->getId()
                );
            }

            $this->references[$cacheKey] = $score;
        }

        return $this->references[$cacheKey];
    }

    /**
     * Get all entries that have answered all container attributes
     *
     * @param Container $container
     * @return array
     */
    protected function getCompleteEntriesForContainer(Container $container)
    {
        $completed = [];
        $autodiag = $container->getAutodiag();
        $allEntries = $this->entryRepository->findByAutodiag($autodiag);

        foreach ($allEntries as $entry) {
            $complete = true;

            foreach ($container->getNestedAttributes() as $attribute) {
                /** @var Autodiag\Attribute $attribute */
                $builder = $this->attributeBuilder->getBuilder($attribute->getType());
                $attributeFound = false;

                foreach ($entry->getValues() as $value) {
                    if ($value->getAttribute()->getId() === $attribute->getId()) {
                        if ($builder->isEmpty($value->getValue())) {
                            $complete = false;
                        }
                        $attributeFound = true;
                    }
                }

                $complete = $complete && $attributeFound;
            }

            if ($complete) {
                $completed[] = $entry;
            }
        }

        return $completed;
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
