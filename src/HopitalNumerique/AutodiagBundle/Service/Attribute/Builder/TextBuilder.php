<?php
namespace HopitalNumerique\AutodiagBundle\Service\Attribute\Builder;

use HopitalNumerique\AutodiagBundle\Form\Type\Attribute\TextType;

/**
 * Text attribute builder
 *
 * @package HopitalNumerique\AutodiagBundle\Service\Attribute\Builder
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class TextBuilder extends AbstractBuilder
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'texte';
    }

    public function transform($data)
    {
        return $data;
    }

    public function reverseTransform($data)
    {
        return $data;
    }

    public function getFormType()
    {
        return TextType::class;
    }
}
