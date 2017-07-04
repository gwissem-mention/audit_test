<?php

namespace HopitalNumerique\RechercheParcoursBundle\Form\Type\Risk;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\Risk\SendSynthesisCommand;

class SendSynthesisType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('recipient', EmailType::class)
            ->add('sender', EmailType::class)
            ->add('subject', TextType::class)
            ->add('content', TextareaType::class)
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SendSynthesisCommand::class,
            'label_format' => 'step.synthesis.actions.mail.form.%name%.label',
            'translation_domain' => 'guided_search',
        ]);
    }
}
