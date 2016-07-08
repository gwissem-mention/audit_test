<?php
namespace HopitalNumerique\AutodiagBundle\Service\Attribute\Builder;

use HopitalNumerique\AutodiagBundle\Form\Type\Attribute\SelectType;

/**
 * Select attribute builder
 *
 * @package HopitalNumerique\AutodiagBundle\Service\Attribute\Builder
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class SelectBuilder extends AbstractBuilder
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'liste';
    }

    public function getFormType()
    {
        return SelectType::class;
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
