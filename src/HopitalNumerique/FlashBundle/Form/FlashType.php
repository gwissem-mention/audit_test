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
            ->add('title', 'text', [
                'required' => true,
                'label' => 'Titre',
            ])
            ->add('content', 'textarea', [
                'required' => true,
                'label' => 'Contenu du message',
            ])
            ->add('roles', 'entity', [
                'class' => 'NodevoRoleBundle:Role',
                'property' => 'name',
                'multiple' => true,
                'required' => false,
                'label' => 'Accessible aux groupes',
                'attr' => ['placeholder' => 'Selectionnez le ou les rôles qui auront accès à cette publication'],
            ])
            ->add('published', 'checkbox', [
                'required' => false,
                'label' => 'Publié ?',
                'attr' => ['class' => 'checkbox', 'style' => 'margin-top:10px'],
            ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\FlashBundle\Entity\Flash',
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_flash_flash';
    }
}
