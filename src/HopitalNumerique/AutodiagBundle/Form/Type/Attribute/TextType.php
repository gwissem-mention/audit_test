<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type\Attribute;

class TextType extends AttributeType
{
    public function getParent()
    {
        return \Symfony\Component\Form\Extension\Core\Type\TextType::class;
    }
}
