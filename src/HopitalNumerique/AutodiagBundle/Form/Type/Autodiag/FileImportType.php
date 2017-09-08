<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type\Autodiag;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FileImportType
 */
class FileImportType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'attr' => ['class' => 'validate[required]'],
            ])
            ->add('notify_update', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('reason', TextType::class, [
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'ad.autodiag.import.update_reason',
                    'class' => 'validate[required]',
                ],
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\AutodiagBundle\Model\AutodiagFileImport',
            'label_format' => 'ad.autodiag.import.%name%',
        ]);
    }
}
