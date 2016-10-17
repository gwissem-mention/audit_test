<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type\Autodiag;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'attr' => ['class' => 'validate[required]']
            ])
            ->add('notify_update', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\AutodiagBundle\Model\AutodiagFileImport',
            'label_format' => 'ad.autodiag.import.%name%'
        ));
    }
}
