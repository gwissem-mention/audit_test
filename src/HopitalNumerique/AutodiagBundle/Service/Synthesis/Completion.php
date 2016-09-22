<?php

namespace HopitalNumerique\AutodiagBundle\Service\Synthesis;

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
    protected $autodiagAttributesCount = [];

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
            foreach ($autodiag->getAttributes() as $attribute) {
                /** @var Attribute $attribute */

                $builder = $this->attributeBuilder->getBuilder($attribute->getType());
                $completion[$attribute->getId()] = false;
                foreach ($synthesis->getEntries() as $entry) {
                    /** @var AutodiagEntry $entry */

                    foreach ($entry->getValues() as $value) {
                        /** @var AutodiagEntry\Value $value */

                        $isEmpty = $builder->isEmpty($value->getValue());
                        if ($value->getAttribute()->getId() === $attribute->getId() && !$isEmpty) {
                            $completion[$attribute->getId()] = true;
                            break 2;
                        }
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
        return $this->valueRepository->getGlobalCompletion($synthesis);
    }

    public function getAttributesCount(Container $container)
    {
        $autodiagId = $container->getAutodiag()->getId();
        if (!array_key_exists($autodiagId, $this->autodiagAttributesCount)) {
            $this->autodiagAttributesCount[$autodiagId] = $this->attributeRepository->countForAutodiag(
                $container->getAutodiag()
            );
        }

        return array_sum(
            array_intersect_key(
                $this->autodiagAttributesCount[$autodiagId],
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
