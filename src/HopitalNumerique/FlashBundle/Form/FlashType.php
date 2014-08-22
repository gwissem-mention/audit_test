<?php

namespace HopitalNumerique\FlashBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FlashType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                'required'   => true, 
                'label'      => 'Titre'
            ))
            ->add('content', 'textarea', array(
                'required'   => true, 
                'label'      => 'Contenu du message'
            ))
            ->add('roles', 'entity', array(
                'class'    => 'NodevoRoleBundle:Role',
                'property' => 'name',
                'multiple' => true,
                'required' => false,
                'label'    => 'Accessible aux groupes',
                'attr'     => array( 'placeholder' => 'Selectionnez le ou les rôles qui auront accès à cette publication' )
            ))
            ->add('published', 'checkbox', array(
                'required' => false,
                'label'    => 'Publié ?',
                'attr'     => array( 'class'=> 'checkbox', 'style' => 'margin-top:10px' )
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\FlashBundle\Entity\Flash'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_flash_flash';
    }
}
