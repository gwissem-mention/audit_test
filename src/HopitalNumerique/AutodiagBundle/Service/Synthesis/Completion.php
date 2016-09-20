<?php

namespace HopitalNumerique\AutodiagBundle\Service\Synthesis;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderProvider;

class Completion
{
    /** @var AttributeBuilderProvider */
    protected $attributeBuilder;

    protected $completion = [];

    /**
     * Completion constructor.
     * @param AttributeBuilderProvider $attributeBuilder
     */
    public function __construct(AttributeBuilderProvider $attributeBuilder)
    {
        $this->attributeBuilder = $attributeBuilder;
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
        $completions = $this->getGlobalCompletion($synthesis);

        $complete = count(array_filter($completions, function ($element) {
            return $element === true;
        }));

        return floor($complete / max(1, count($completions)) * 100);
    }

    public function getAnswersCount(Synthesis $synthesis, Container $container)
    {
        $completions = $this->getGlobalCompletion($synthesis);
        $answers = 0;
        foreach ($container->getAttributes() as $attribute) {
            if (array_key_exists($attribute->getId(), $completions) && true === $completions[$attribute->getId()]) {
                $answers++;
            }
        }
        return $answers;
    }

}
