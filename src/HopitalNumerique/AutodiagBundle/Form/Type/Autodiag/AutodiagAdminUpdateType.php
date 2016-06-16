<?php
namespace HopitalNumerique\AutodiagBundle\Form\Type\Autodiag;

use HopitalNumerique\AutodiagBundle\Form\Type\Autodiag\PresetType;
use HopitalNumerique\AutodiagBundle\Form\Type\AutodiagType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * ModelAdminUpdateType represent the admin form for create and update Model
 *
 * @package HopitalNumerique\AutodiagBundle\Form\Type\Domain
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class AutodiagAdminUpdateType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('autodiag', AutodiagType::class)
            ->add('presets', CollectionType::class, [
                'entry_type' => PresetType::class,
            ])
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\AutodiagBundle\Model\AutodiagAdminUpdate',
            'label_format' => 'ad.autodiag.%name%'
        ));
    }
}
