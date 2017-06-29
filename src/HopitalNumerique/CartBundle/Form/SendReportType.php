<?php

namespace HopitalNumerique\CartBundle\Form;

use HopitalNumerique\CartBundle\Enum\ReportColumnsEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SendReportType extends AbstractType
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
            ->add('subject')
            ->add('content', TextareaType::class)
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'HopitalNumerique\CartBundle\Domain\Command\SendReportCommand',
            'label_format'       => 'sendReportForm.fields.%name%.label',
            'translation_domain' => 'cart',
            'csrf_protection'    => false,
            'required'           => true,
        ]);
    }
}
