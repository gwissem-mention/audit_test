<?php

namespace HopitalNumerique\CartBundle\Form;

use HopitalNumerique\CartBundle\Enum\ReportColumnsEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ReportType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('columns', ChoiceType::class, [
                'choices' => ReportColumnsEnum::getColumns(),
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('submit', SubmitType::class)
            ->add('abort', SubmitType::class)
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'HopitalNumerique\CartBundle\Domain\Command\GenerateReportCommand',
            'label_format'       => 'form.fields.%name%.label',
            'translation_domain' => 'cart',
            'csrf_protection'    => false,
        ]);
    }
}
