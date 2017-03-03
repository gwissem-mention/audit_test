<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type\Attribute;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

class SelectChoiceLoader implements ChoiceLoaderInterface
{
    protected $options;

    public function __construct(Attribute $attribute)
    {
        $this->options = $attribute->getOptions();
    }

    public function loadChoiceList($value = null)
    {
        $choices = [];
        foreach ($this->options as $option) {
            /* @var Attribute\Option $option */
            $choices[$option->getLabel()] = (string) $option->getValue();
        }

        return new ArrayChoiceList($choices, $value);
    }

    public function loadChoicesForValues(array $values, $value = null)
    {
        return $this->loadChoiceList()->getChoicesForValues($values, $value);
    }

    public function loadValuesForChoices(array $choices, $value = null)
    {
        return $this->loadChoiceList()->getValuesForChoices($choices);
    }
}
