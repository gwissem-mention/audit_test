<?php

namespace HopitalNumerique\AutodiagBundle\Service\Synthesis;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Repository\Autodiag\AttributeRepository;
use HopitalNumerique\AutodiagBundle\Repository\AutodiagEntry\ValueRepository;
use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderProvider;

class Completion
{
    /** @var AttributeBuilderProvider */
    protected $attributeBuilder;

    /** @var ValueRepository */
    protected $valueRepository;

    /** @var AttributeRepository */
    protected $attributeRepository;

    protected $completion = [];
    protected $answersCount = [];
    protected $autodiagAttributesCount = null;

    /**
     * Completion constructor.
     * @param AttributeBuilderProvider $attributeBuilder
     * @param ValueRepository $valueRepository
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(
        AttributeBuilderProvider $attributeBuilder,
        ValueRepository $valueRepository,
        AttributeRepository $attributeRepository
    ) {
        $this->attributeBuilder = $attributeBuilder;
        $this->valueRepository = $valueRepository;
        $this->attributeRepository = $attributeRepository;
    }

    public function getGlobalCompletion(Synthesis $synthesis)
    {

        if (!array_key_exists($synthesis->getId(), $this->completion)) {
            $completion = [];
            $autodiag = $synthesis->getAutodiag();

            $values = $this->valueRepository->getSynthesisValues($synthesis);

            foreach ($autodiag->getAttributes() as $attribute) {
                /** @var Attribute $attribute */

                $builder = $this->attributeBuilder->getBuilder($attribute->getType());
                $completion[$attribute->getId()] = false;
                foreach ($values as $value) {
                    /** @var AutodiagEntry\Value $value */

                    $isEmpty = $builder->isEmpty($value['value']);
                    if ($value['attribute_id'] === $attribute->getId() && !$isEmpty) {
                        $completion[$attribute->getId()] = true;
                        break;
                    }
                }
            }

            $this->completion[$synthesis->getId()] = $completion;
        }

        return $this->completion[$synthesis->getId()];
    }

    /**
     * Retourne le taux de complétion d'une synthèse
     *
     * @param Synthesis $synthesis
     * @return int
     */
    public function getCompletionRate(Synthesis $synthesis)
    {
        $completions = $this->getGlobalCompletion($synthesis);

        $complete = count(array_filter($completions, function ($element) {
            return $element === true;
        }));

        return floor($complete / max(1, count($completions)) * 100);
    }

    public function getAttributesCount(Container $container)
    {
        if (null === $this->autodiagAttributesCount) {
            $this->autodiagAttributesCount = $this->attributeRepository->countForAutodiag($container->getAutodiag());
        }

        return array_sum(
            array_intersect_key(
                $this->autodiagAttributesCount,
                array_flip($container->getNestedContainerIds())
            )
        );
    }

    public function getAnswersCount(Synthesis $synthesis, Container $container)
    {
        if (!array_key_exists($synthesis->getId(), $this->answersCount)) {
            $this->answersCount[$synthesis->getId()] = $this->valueRepository->getAnswersCount($synthesis);
        }

        return array_sum(
            array_intersect_key(
                $this->answersCount[$synthesis->getId()],
                array_flip($container->getNestedContainerIds())
            )
        );
    }

    public function isComplete(Synthesis $synthesis, Container $container)
    {
        return $this->getAttributesCount($container) == $this->getAnswersCount($synthesis, $container);
    }
}
