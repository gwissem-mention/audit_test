<?php
namespace HopitalNumerique\AutodiagBundle\Service;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Category;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Item as RestitutionItem;
use HopitalNumerique\AutodiagBundle\Model\Result\Item as ResultItem;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Model\Result\ItemActionPlan;
use HopitalNumerique\AutodiagBundle\Model\Result\ItemAttribute;
use HopitalNumerique\AutodiagBundle\Model\Result\Score;
use HopitalNumerique\AutodiagBundle\Repository\AutodiagEntryRepository;
use HopitalNumerique\AutodiagBundle\Repository\RestitutionRepository;
use HopitalNumerique\AutodiagBundle\Service\Algorithm\ReferenceAlgorithm;
use \HopitalNumerique\AutodiagBundle\Service\Algorithm\Score as Algorithm;
use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderProvider;
use HopitalNumerique\AutodiagBundle\Service\Attribute\PresetableAttributeBuilderInterface;
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
     * @var RestitutionRepository
     */
    protected $restitutionRepository;

    /**
     * @var Completion
     */
    protected $completion;

    protected $items = [];
    protected $references = [];
    protected $attributeResponses = [];
    protected $completeEntriesByContainer = [];
    protected $entriesByAutodiag = [];


    /**
     * RestitutionCalculator constructor.
     * @param Algorithm $algorithm
     * @param AttributeBuilderProvider $attributeBuilder
     * @param AutodiagEntryRepository $entryRepository
     * @param RestitutionRepository $restitutionRepository
     * @param Completion $completion
     * @TODO Injecter un algorithm factory qui connaitrais tous les algo possible via un compileur pass
     */
    public function __construct(
        Algorithm $algorithm,
        AttributeBuilderProvider $attributeBuilder,
        AutodiagEntryRepository $entryRepository,
        RestitutionRepository $restitutionRepository,
        Completion $completion
    ) {
        $this->algorithm = $algorithm;
        $this->attributeBuilder = $attributeBuilder;
        $this->entryRepository = $entryRepository;
        $this->restitutionRepository = $restitutionRepository;
        $this->completion = $completion;
    }

    public function compute(Synthesis $synthesis)
    {
        $this->attributeResponses[$synthesis->getId()] = $this->completion->getGlobalCompletion($synthesis);

        $autodiag = $synthesis->getAutodiag();

        $restitution = $this->restitutionRepository->getForAutodiag($autodiag);

        $result = [];

        foreach ($restitution->getCategories() as $category) {
            /** @var Category $category */
            foreach ($category->getItems() as $item) {
                /** @var RestitutionItem $item */
                $result[$item->getId()] = $this->computeItem($item, $synthesis);
            }
        }

        foreach ($autodiag->getChapters() as $chapter) {
            $cacheKey = $this->getCacheKey(
                $autodiag,
                $chapter,
                $synthesis
            );

            if (!array_key_exists($cacheKey, $this->items)) {
                $this->computeItemContainer($chapter, $synthesis, [], true);
            }
        }

        return $result;
    }

    /**
     * Traite un item de la page de restitution (représente 1 graphique d'un onglet de la page de resultat)
     *
     * @param RestitutionItem $item
     * @param Synthesis $synthesis
     * @return array
     */
    protected function computeItem(RestitutionItem $item, Synthesis $synthesis)
    {
        $result = [
            'items' => [],
            'references' => [],
        ];

        $containers = $item->getContainers();
        foreach ($containers as $container) {
            /** @var Container $container */

            $references = $item->getReferences();

            // Dans le cas d'une synthèse (de plusieurs entries),on n'affiche plus les références de l'AD mais le min et
            // max de la synthèse
            if ($synthesis->getEntries()->count() > 1) {
                $references = [
                    (new Autodiag\Reference('min', $synthesis->getAutodiag()))->setValue('min')->setLabel('Minimum'),
                    (new Autodiag\Reference('max', $synthesis->getAutodiag()))->setValue('max')->setLabel('Maximum'),
                ];
            }

            $resultItem = $this->computeItemContainer($container, $synthesis, $references);

            foreach ($references as $reference) {
                $result['references'][$reference->getNumber()] = $reference->getLabel();
            }

            $result['items'][] = $resultItem;
        }

        return $result;
    }

    /**
     * Traite les données d'un container d'autodiag (chapitre ou catégorie) pour une synthèse donnée
     *
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

            $actionPlan = $this->getContainerActionPlan($synthesis->getAutodiag(), $container, $score);
            if ($actionPlan) {
                $resultItem->setActionPlan($actionPlan);
            }

            // Traitement des questions / réponses
            $colorationInversed = 0;
            foreach ($container->getAttributes() as $attribute) {

                $colorationInversed += $attribute->isColorationInversed() ? 1 : -1;

                /** @var Autodiag\Attribute $attribute */
                $builder = $this->attributeBuilder->getBuilder($attribute->getType());

                $itemAttribute = new ItemAttribute($attribute->getLabel());
                $itemAttribute->setColorationInversed($attribute->isColorationInversed());
                $resultItem->addAttribute($itemAttribute);

                if ($builder instanceof PresetableAttributeBuilderInterface) {
                    $options = $builder->getPreset($synthesis->getAutodiag())->getPreset();
                } else {
                    $attributeOptions = $attribute->getOptions();
                    $options = [];
                    foreach ($attributeOptions as $option) {
                        $options[$option->getValue()] = $option->getLabel();
                    }
                }

                foreach ($synthesis->getEntries() as $entry) {
                    /** @var AutodiagEntry $entry*/
                    foreach ($entry->getValues() as $entryValue) {
                        if ($entryValue->getAttribute()->getId() === $attribute->getId()) {
                            $response = $builder->transform($entryValue->getValue());
                            if (is_array($response)) {

                                $responseValue = array_sum($response) / count($response);
                                foreach ($response as $code => &$value) {
                                    if (null === $value) {
                                        $value = " - ";
                                    } else {
                                        $value = isset($options[$code]) ? $options[$code][$value] : $value;
                                    }
                                }
                                $response = implode(' - ', $response);
                            } else {
                                $responseValue = $response;
                                if (isset($options[$response])) {
                                    $response = $options[$response];
                                } elseif (null === $response) {
                                    $response = "-";
                                }
                            }

                            $itemAttribute->setResponse(
                                $responseValue,
                                $response
                            );
                        }
                    }
                }

                $actionPlan = $this->getAttributeActionPlan($synthesis->getAutodiag(), $attribute, $responseValue);
                if ($actionPlan) {
                    $itemAttribute->setActionPlan($actionPlan);
                }
            }
            $resultItem->setColorationInversed($colorationInversed > 0);

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
                    $reference->getNumber()
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
        $cacheKey = implode('-', [
            $container->getCode(),
        ]);

        if (!array_key_exists($cacheKey, $this->completeEntriesByContainer)) {

            $completed = [];
            $autodiag = $container->getAutodiag();
            $allEntries = $this->getAutodiagEntries($autodiag);

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

            $this->completeEntriesByContainer[$cacheKey] = $completed;
        }

        return $this->completeEntriesByContainer[$cacheKey];
    }

    /**
     * Get all entries for Autodiag
     *
     * @param Autodiag $autodiag
     * @return mixed
     */
    protected function getAutodiagEntries(Autodiag $autodiag)
    {
        $cacheKey = $autodiag->getId();

        if (!array_key_exists($cacheKey, $this->entriesByAutodiag)) {
            $this->entriesByAutodiag[$cacheKey] = $this->entryRepository->findOriginalByAutodiag($autodiag);
        }

        return $this->entriesByAutodiag[$cacheKey];
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

    /**
     * @param Autodiag\ActionPlan[] $plans
     * @param $score
     * @return null
     */
    protected function findActionPlan($plans, $score)
    {
        if (empty($plans)) {
            return null;
        }

        $closest = null;
        foreach ($plans as $plan) {
            if ($score < $plan->getValue()) {
                if (null === $closest || $plan->getValue() < $closest->getValue()) {
                    $closest = $plan;
                }
            }
        }

        if (null === $closest) {
            return null;
        }

        $actionPlan = new ItemActionPlan(
            $closest->getValue(),
            $closest->getDescription(),
            $closest->getLink(),
            $closest->getLinkDescription(),
            $closest->isVisible()
        );

        return $actionPlan;
    }

    protected function getContainerActionPlan(Autodiag $autodiag, Container $container, $score)
    {
        $plans = $autodiag->getActionPlans()->filter(function (Autodiag\ActionPlan $actionPlan) use ($container) {
            return $actionPlan->getContainer() == $container;
        });

        return $this->findActionPlan($plans->toArray(), $score);
    }

    protected function getAttributeActionPlan(Autodiag $autodiag, Autodiag\Attribute $attribute, $score)
    {
        $plans = $autodiag->getActionPlans()->filter(function (Autodiag\ActionPlan $actionPlan) use ($attribute) {
            return $actionPlan->getAttribute() == $attribute;
        });

        return $this->findActionPlan($plans->toArray(), $score);
    }

    protected function getCacheKey(Autodiag $autodiag, Container $container, Synthesis $synthesis)
    {
        return $autodiag->getId()
        . $container->getId()
        . $synthesis->getId();
    }
}
