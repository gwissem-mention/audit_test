<?php
namespace HopitalNumerique\AutodiagBundle\Form\Type\Domain;

use HopitalNumerique\AutodiagBundle\Form\Type\Model\PresetType;
use HopitalNumerique\AutodiagBundle\Form\Type\ModelType;
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
class ModelAdminUpdateType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('model', ModelType::class)
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
            'data_class' => 'HopitalNumerique\AutodiagBundle\Domain\ModelAdminUpdate',
            'label_format' => 'ad.model.%name%'
        ));
    }
}
