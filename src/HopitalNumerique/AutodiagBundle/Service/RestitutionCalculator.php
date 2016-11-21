<?php
namespace HopitalNumerique\AutodiagBundle\Service;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\Restitution;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Category;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Item as RestitutionItem;
use HopitalNumerique\AutodiagBundle\Model\Autodiag\SynthesisReference;
use HopitalNumerique\AutodiagBundle\Model\Result\Item as ResultItem;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Model\Result\Item;
use HopitalNumerique\AutodiagBundle\Model\Result\ItemActionPlan;
use HopitalNumerique\AutodiagBundle\Model\Result\ItemAttribute;
use HopitalNumerique\AutodiagBundle\Model\Result\Score;
use HopitalNumerique\AutodiagBundle\Repository\Autodiag\AttributeRepository;
use HopitalNumerique\AutodiagBundle\Repository\AutodiagEntry\ValueRepository;
use HopitalNumerique\AutodiagBundle\Repository\RestitutionRepository;
use HopitalNumerique\AutodiagBundle\Repository\ScoreRepository;
use HopitalNumerique\AutodiagBundle\Service\Algorithm\ReferenceAlgorithm;
use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderProvider;
use HopitalNumerique\AutodiagBundle\Service\Attribute\PresetableAttributeBuilderInterface;
use HopitalNumerique\AutodiagBundle\Service\Synthesis\Completion;

/**
 * Class RestitutionCalculator
 * @package HopitalNumerique\AutodiagBundle\Service
 * @TODO Utiliser le ResultItemBuilder pour construire les ResultItem, Attribute, calcul du score etc
 */
class RestitutionCalculator
{
    /**
     * @var AttributeBuilderProvider
     */
    protected $attributeBuilder;

    /**
     * @var ValueRepository
     */
    protected $valueRepository;

    /**
     * @var RestitutionRepository
     */
    protected $restitutionRepository;

    /**
     * @var ScoreRepository
     */
    protected $scoreRepository;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var Completion
     */
    protected $completion;

    /** @var Restitution */
    private $restitution;

    protected $items = [];
    protected $references = [];
    protected $synthesisScores = [];
    protected $allScores = [];
    protected $autodiagAttributesCount = null;
    protected $responses = [];
    protected $minAndMaxForAutodiagAttributes = null;
    protected $resultItemCreatedCallback;

    /**
     * RestitutionCalculator constructor.
     * @param AttributeBuilderProvider $attributeBuilder
     * @param ValueRepository $valueRepository
     * @param RestitutionRepository $restitutionRepository
     * @param Completion $completion
     * @param ScoreRepository $scoreRepository
     * @param AttributeRepository $attributeRepository
     * @TODO Injecter un algorithm factory qui connaitrais tous les algo possible via un compileur pass
     */
    public function __construct(
        AttributeBuilderProvider $attributeBuilder,
        ValueRepository $valueRepository,
        RestitutionRepository $restitutionRepository,
        Completion $completion,
        ScoreRepository $scoreRepository,
        AttributeRepository $attributeRepository
    ) {
        $this->attributeBuilder = $attributeBuilder;
        $this->valueRepository = $valueRepository;
        $this->restitutionRepository = $restitutionRepository;
        $this->completion = $completion;
        $this->scoreRepository = $scoreRepository;
        $this->attributeRepository = $attributeRepository;
    }

    public function compute(Synthesis $synthesis)
    {
        $autodiag = $synthesis->getAutodiag();

        $result = [];

        foreach ($this->getRestitution($autodiag)->getCategories() as $category) {
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
                $this->computeItemContainer($chapter, $synthesis, []);
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
    public function computeItem(RestitutionItem $item, Synthesis $synthesis)
    {
        $result = [
            'items' => [],
            'references' => [],
        ];

        $references = $item->getReferences();
        // Dans le cas d'une synthèse (de plusieurs entries),on n'affiche plus les références de l'AD mais le min et
        // max de la synthèse
        if ($synthesis->getEntries()->count() > 1) {
            $references = [
                SynthesisReference::create($synthesis, 'min')->setValue('min')->setLabel('Minimum'),
                SynthesisReference::create($synthesis, 'max')->setValue('max')->setLabel('Maximum'),
            ];
        }

        $containers = $item->getContainers();
        foreach ($containers as $container) {
            /** @var Container $container */
            $resultItem = $this->computeItemContainer($container, $synthesis, $references);

            foreach ($references as $reference) {
                $result['references'][$reference->getNumber()] = $reference->getLabel();
            }

            $result['items'][] = $resultItem;
        }

        if ($item->getPriority() === Item::ITEM_PRIORITY_PRIORISE) {
            usort($result['items'], function (Item $a, Item $b) {
                return $b->getScore()->getValue() === null
                    ? 1
                    : $a->getScore()->getValue() > $b->getScore()->getValue();
            });
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
    public function computeItemContainer(Container $container, Synthesis $synthesis, $references = [])
    {
        $cacheKey = $this->getCacheKey(
            $container->getAutodiag(),
            $container,
            $synthesis
        );

        if (!array_key_exists($cacheKey, $this->items)) {
            $score = $this->getContainerSynthesisScore($synthesis, $container->getId());
            $resultItem = new ResultItem();
            $resultItem->setLabel($container->getExtendedLabel());
            $resultItem->setScore(
                new Score(
                    $score,
                    $this->getRestitution($container->getAutodiag())->getScoreLabel(),
                    null,
                    $this->getRestitution($container->getAutodiag())->getScoreColor()
                )
            );

            $resultItem->setNumberOfQuestions($this->completion->getAttributesCount($container));
            $resultItem->setNumberOfAnswers($this->completion->getAnswersCount($synthesis, $container));

            $actionPlan = $this->getContainerActionPlan($synthesis->getAutodiag(), $container, $score);
            if ($actionPlan) {
                $resultItem->setActionPlan($actionPlan);
            }

            // Traitement des questions / réponses
            $colorationInversed = 0;
            foreach ($this->getResponses($synthesis, $container) as $attribute) {
                $colorationInversed += $attribute['colorationInversed'] ? 1 : -1;

                if ($synthesis->getEntries()->count() === 1) {
                    $this->computeResultItemAttribute($resultItem, $synthesis, $attribute);
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


            if (is_callable($this->resultItemCreatedCallback)) {
                call_user_func($this->resultItemCreatedCallback, $resultItem, $container);
            }

            $this->items[$cacheKey] = $resultItem;
        }

        return $this->items[$cacheKey];
    }

    public function computeResultItemAttribute(ResultItem $item, Synthesis $synthesis, array $attribute)
    {
        $builder = $this->attributeBuilder->getBuilder($attribute['type']);

        $itemAttribute = new ItemAttribute(
            sprintf('%s. %s', $attribute['code'], $attribute['attribute_label'])
        );
        $itemAttribute->attributeId = $attribute['attribute_id'];
        $itemAttribute->setColorationInversed($attribute['colorationInversed']);
        $item->addAttribute($itemAttribute);

        $responseText = $attribute['option_label'];

        if (null === $responseText && null !== $attribute['value_value']) {
            if ($builder instanceof PresetableAttributeBuilderInterface) {
                $preset = $builder->getPreset($synthesis->getAutodiag());

                if (null !== $preset) {
                    $preset = $preset->getPreset();
                    $response = $builder->transform($attribute['value_value']);

                    $responseText = [];
                    foreach ($response as $key => $value) {
                        if (array_key_exists($key, $preset) && array_key_exists($value, $preset[$key])) {
                            $responseText[] = $preset[$key][$value];
                        } else {
                            $responseText[] = null;
                        }
                    }
                    $responseText = implode(' - ', $responseText);
                }
            }
        }

        $itemAttribute->setResponse(
            $builder->computeScore($attribute['value_value']),
            $responseText === null && $attribute['entry_id'] !== null ? 'Non concerné' : $responseText,
            $attribute['value_comment']
        );

        if (null !== $itemAttribute->response->getValue()) {
            $actionPlan = $this->getAttributeActionPlan(
                $synthesis->getAutodiag(),
                $attribute['attribute_id'],
                $itemAttribute->response->getValue()
            );

            if ($actionPlan) {
                $itemAttribute->setActionPlan($actionPlan);
            }
        }

        $score =
            $this->calculateScore(
                $itemAttribute->response->getValue(),
                $this->getMinAndMaxForAutodiagAttributes($synthesis->getAutodiag(), $attribute)
            )
        ;

        $itemAttribute->response->setScore($score);
    }

    protected function getReferenceScore(Autodiag\Reference $reference, Container $container)
    {
        $cacheKey = implode('-', [
            $reference->getValue(),
            $container->getId(),
        ]);

        if (!array_key_exists($cacheKey, $this->references)) {

            $score = null;

            if ($reference instanceof SynthesisReference) {

                $score = new Score(
                    $this->getContainerSynthesisScore($reference->getSynthesis(), $container->getId(), $reference->getNumber()),
                    $reference->getLabel(),
                    $reference->getNumber(),
                    $reference->getColor()
                );
            } else {
                $referenceScores = $this->getContainerScores($container);
                if (count($referenceScores) > 0) {
                    $score = new Score(
                        ReferenceAlgorithm::compute($reference, $referenceScores),
                        $reference->getLabel(),
                        $reference->getNumber(),
                        $reference->getColor()
                    );
                }
            }



            $this->references[$cacheKey] = $score;
        }

        return $this->references[$cacheKey];
    }

    public function getContainerSynthesisScore(Synthesis $synthesis, $containerId, $type = 'score')
    {
        if (!array_key_exists($synthesis->getId(), $this->synthesisScores)) {
            $this->synthesisScores[$synthesis->getId()] = $this->scoreRepository->getScores($synthesis);
        }

        return array_key_exists($containerId, $this->synthesisScores[$synthesis->getId()])
            ? $this->synthesisScores[$synthesis->getId()][$containerId][$type]
            : null;
    }

    /**
     * Retourne la valeur minimale et maximale des réponses de l'attribut (pour l'autodiag en paramètre)
     *
     * @param Autodiag $autodiag
     * @param array $attribute
     * @return mixed
     */
    protected function getMinAndMaxForAutodiagAttributes(Autodiag $autodiag, array $attribute)
    {
        if ($this->minAndMaxForAutodiagAttributes == null) {
            $this->minAndMaxForAutodiagAttributes =
                $this
                    ->attributeRepository
                    ->getMinAndMaxForAutodiagByAttributes($autodiag)
            ;
        }

        if ($this->minAndMaxForAutodiagAttributes[$attribute['attribute_id']]['minimum'] == null
            && $this->minAndMaxForAutodiagAttributes[$attribute['attribute_id']]['maximum'] == null) {
            $builder = $this->attributeBuilder->getBuilder($attribute['type']);
            if ($builder instanceof PresetableAttributeBuilderInterface) {
                $this->minAndMaxForAutodiagAttributes[$attribute['attribute_id']]['minimum'] = $builder->getPresetMinScore($autodiag);
                $this->minAndMaxForAutodiagAttributes[$attribute['attribute_id']]['maximum'] = $builder->getPresetMaxScore($autodiag);
            }
        }

        return $this->minAndMaxForAutodiagAttributes[$attribute['attribute_id']];
    }

    protected function getContainerScores(Container $container)
    {
        if (!array_key_exists($container->getId(), $this->allScores)) {
            $this->allScores[$container->getId()] = $this->scoreRepository->getReferenceScores($container);
        }

        return $this->allScores[$container->getId()];
    }

    public function getResponses(Synthesis $synthesis, Container $container)
    {
        if (!array_key_exists($synthesis->getId(), $this->responses)) {
            $this->responses[$synthesis->getId()] = $this->valueRepository->getFullValuesByEntry(
                $synthesis->getAutodiag()->getId(),
                $synthesis->getEntries()->first()->getId()
            );
        }

        foreach ($this->responses[$synthesis->getId()] as $response) {
            if (in_array($container->getId(), $response['container_id'])) {
                yield $response;
            }
        }
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
            // -1 correspond à une réponse de type "Non concerné"
            // donc on n'affiche pas de plan d'action pour cette réponse
            if ($score <= $plan->getValue() && $score != '-1') {
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

    protected function getAttributeActionPlan(Autodiag $autodiag, $attributeId, $score)
    {
        $plans = $autodiag->getActionPlans()->filter(function (Autodiag\ActionPlan $actionPlan) use ($attributeId) {
            return null !== $actionPlan->getAttribute() && $actionPlan->getAttribute()->getId() == $attributeId;
        });

        return $this->findActionPlan($plans->toArray(), $score);
    }

    protected function getCacheKey(Autodiag $autodiag, Container $container, Synthesis $synthesis)
    {
        return $autodiag->getId()
        . $container->getId()
        . $synthesis->getId();
    }

    /**
     * Calcule le score d'une valeur en fonction des valeurs min et max
     *
     * @param $value
     * @param $minAndMax
     * @return float
     */
    protected function calculateScore($value, $minAndMax)
    {
        if ($value === null) {
            return null;
        }

        $a = ($minAndMax['maximum'] - $minAndMax['minimum']) / 100;
        $b = $minAndMax['maximum'];
        $x = ($value - $b) / $a;

        return 100 + $x;
    }

    protected function getRestitution(Autodiag $autodiag)
    {
        if (null === $this->restitution) {
            $this->restitution = $this->restitutionRepository->getForAutodiag($autodiag);
        }

        return $this->restitution;
    }

    public function setResultItemCreatedCallback($callback)
    {
        $this->resultItemCreatedCallback = $callback;
    }
}
