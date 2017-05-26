<?php

namespace HopitalNumerique\AutodiagBundle\Service\Result;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Model\Result\Item;
use HopitalNumerique\AutodiagBundle\Model\Result\ItemAttribute;
use HopitalNumerique\AutodiagBundle\Repository\Autodiag\AttributeRepository;
use HopitalNumerique\AutodiagBundle\Repository\AutodiagEntry\ValueRepository;
use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderProvider;
use HopitalNumerique\AutodiagBundle\Service\Attribute\PresetableAttributeBuilderInterface;
use HopitalNumerique\AutodiagBundle\Service\Synthesis\Completion;

class ResultItemBuilder
{
    /**
     * @var Completion
     */
    protected $completion;

    /**
     * @var AttributeBuilderProvider
     */
    protected $attributeBuilder;

    /**
     * @var ValueRepository
     */
    protected $valueRepository;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    protected $responses = [];
    protected $minAndMaxForAutodiagAttributes = null;

    public function __construct(Completion $completion, AttributeBuilderProvider $attributeBuilder, ValueRepository $valueRepository, AttributeRepository $attributeRepository)
    {
        $this->completion = $completion;
        $this->attributeBuilder = $attributeBuilder;
        $this->valueRepository = $valueRepository;
        $this->attributeRepository = $attributeRepository;
    }

    public function build(Container $container, Synthesis $synthesis)
    {
        $resultItem = new Item();

        $resultItem->setLabel($container->getExtendedLabel());

        $resultItem->setNumberOfQuestions($this->completion->getAttributesCount($container));
        $resultItem->setNumberOfAnswers($this->completion->getAnswersCount($synthesis, $container));

        $colorationInversed = 0;
        foreach ($this->getResponses($synthesis, $container) as $attribute) {
            $colorationInversed += $attribute['colorationInversed'] ? 1 : -1;

            if ($synthesis->getEntries()->count() === 1) {
                $this->computeResultItemAttribute($resultItem, $synthesis, $attribute);
            }
        }
        $resultItem->setColorationInversed($colorationInversed > 0);

        foreach ($container->getChilds() as $child) {
            $resultItem->addChildren(
                $this->build($child, $synthesis)
            );
        }

        return $resultItem;
    }

    protected function getResponses(Synthesis $synthesis, Container $container)
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

    protected function computeResultItemAttribute(Item $item, Synthesis $synthesis, array $attribute)
    {
        $builder = $this->attributeBuilder->getBuilder($attribute['type']);

        if (null == $attribute['number']) {
            $itemAttribute = new ItemAttribute($attribute['attribute_label']);
        } else {
            $itemAttribute = new ItemAttribute(
                sprintf('%s. %s', $attribute['number'], $attribute['attribute_label'])
            );
        }

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
            Autodiag\Attribute::TEXT_TYPE === $attribute['type'] ? $attribute['unit'] : null,
            $attribute['value_comment']
        );

        $score =
            $this->calculateScore(
                $itemAttribute->response->getValue(),
                $this->getMinAndMaxForAutodiagAttributes($synthesis->getAutodiag(), $attribute)
            )
        ;

        $itemAttribute->response->setScore($score);
    }

    /**
     * Retourne la valeur minimale et maximale des réponses de l'attribut (pour l'autodiag en paramètre).
     *
     * @param Autodiag $autodiag
     * @param array    $attribute
     *
     * @return mixed
     */
    public function getMinAndMaxForAutodiagAttributes(Autodiag $autodiag, array $attribute)
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

    /**
     * Calcule le score d'une valeur en fonction des valeurs min et max.
     *
     * @param $value
     * @param $minAndMax
     *
     * @return float
     */
    public function calculateScore($value, $minAndMax)
    {
        if ($value === null) {
            return null;
        }

        $a = ($minAndMax['maximum'] - $minAndMax['minimum']) / 100;
        $b = $minAndMax['maximum'];
        $x = ($value - $b) / $a;

        return 100 + $x;
    }
}
