<?php

namespace HopitalNumerique\AutodiagBundle\Service\Attribute\Builder;

use HopitalNumerique\AutodiagBundle\Form\Type\Attribute\TextType;

/**
 * Text attribute builder.
 *
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class TextBuilder extends AbstractBuilder
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'texte';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateName()
    {
        return 'text';
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

    public function computeScore($data)
    {
        return null;
    }

    public function isEmpty($data)
    {
        return strlen($data) === 0;
    }
}
