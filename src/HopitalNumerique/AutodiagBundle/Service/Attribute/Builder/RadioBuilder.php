<?php

namespace HopitalNumerique\AutodiagBundle\Service\Attribute\Builder;

use HopitalNumerique\AutodiagBundle\Form\Type\Attribute\RadioAttributeType;

/**
 * Radio attribute builder.
 *
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class RadioBuilder extends AbstractBuilder
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'radio';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateName()
    {
        return 'radio';
    }

    public function getFormType()
    {
        return RadioAttributeType::class;
    }

    public function transform($data)
    {
        return $data;
    }

    public function reverseTransform($data)
    {
        return $data;
    }

    public function computeScore($data)
    {
        $data = $this->transform($data);
        if (null === $data || $data == '-1') {
            return null;
        }

        return (float) $data;
    }

    public function isEmpty($data)
    {
        return null === $data || strlen($data) === 0;
    }
}
