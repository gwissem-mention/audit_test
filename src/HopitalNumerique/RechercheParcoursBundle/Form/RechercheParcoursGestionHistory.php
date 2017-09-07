<?php

namespace HopitalNumerique\RechercheParcoursBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RechercheParcoursGestionHistory extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('notify_update', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('reason', TextType::class, [
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Raison de la mise à jour',
                    'class' => 'validate[required]',
                ],
            ])
            ->add('go', ButtonType::class, [
                'label' => 'OK'
            ])
        ;

    }

    public function getName()
    {
        return 'hopitalnumerique_rechercheparcours_rechercheparcoursgestion_history';
    }
}
