<?php
// src/Nodevo/ToolsBundle/Form/Type/NodevoCommentaireType.php
namespace Nodevo\ToolsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NodevoCommentaireType extends AbstractType
{
    public function getParent()
    {
        return 'textarea';
    }

    public function getName()
    {
        return 'nodevocommentaire';
    }
}
