<?php

namespace HopitalNumerique\RechercheParcoursBundle\Form\Type\Risk;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use HopitalNumerique\ObjetBundle\Entity\Risk;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\IncludeRiskCommand;

class ShowRiskType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $event->getForm()
                ->add('risk', EntityType::class, [
                    'class' => Risk::class,
                    'choice_label' => 'longLabel',
                    'choices' => $event->getData()->guidedSearchStep->getExcludedRisks(),
                ])
            ;
        });

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => IncludeRiskCommand::class,
        ]);
    }
}
