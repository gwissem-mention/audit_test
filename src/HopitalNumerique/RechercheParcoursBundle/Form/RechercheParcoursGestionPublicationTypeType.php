<?php

namespace HopitalNumerique\RechercheParcoursBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class RechercheParcoursGestionPublicationTypeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('order',HiddenType::class, [
                'property_path' => '[order]',
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('active', CheckboxType::class, [
                'property_path' => '[active]',
            ])
        ;
    }

    public function getName()
    {
        return 'hopitalnumerique_rechercheparcours_rechercheparcoursgestion_publication_type';
    }
}
