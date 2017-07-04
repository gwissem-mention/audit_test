<?php

namespace HopitalNumerique\RechercheParcoursBundle\Form\Type\GuidedSearch;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\GuidedSearch\ShareGuidedSearchCommand;

class ShareGuidedSearchType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'step.share.modal.form.email.label',
            ])
            ->add('initialData', CheckboxType::class, [
                'label' => 'step.share.modal.form.initial_data.label',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ShareGuidedSearchCommand::class,
            'translation_domain' => 'guided_search',
        ]);
    }
}
