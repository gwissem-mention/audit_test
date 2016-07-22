<?php
namespace HopitalNumerique\AutodiagBundle\Service\Attribute\Builder;

use HopitalNumerique\AutodiagBundle\Form\Type\Attribute\RadioAttributeType;

/**
 * Radio attribute builder
 *
 * @package HopitalNumerique\AutodiagBundle\Service\Attribute\Builder
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class RadioBuilder extends AbstractBuilder
{
    /**
     * @inheritdoc
     */
    public function getName()
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
}
