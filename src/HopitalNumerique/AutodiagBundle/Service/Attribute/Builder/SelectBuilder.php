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

    /**
     * @inheritdoc
     */
    public function getTemplateName()
    {
        return 'select';
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

    public function computeScore($data)
    {
        return null !== $data ? (float) $data : null;
    }

    public function isEmpty($data)
    {
        return false;
    }
}
