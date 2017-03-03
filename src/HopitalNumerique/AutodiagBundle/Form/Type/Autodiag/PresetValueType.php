<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type\Autodiag;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Base Preset value Type.
 *
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class PresetValueType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function ($value) {
                return $value;
            },
            function ($value) {
                return array_sum(array_map('count', $value)) === 0 ? null : $value;
            }
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'label_format' => 'ad.autodiag.preset_value.%name%',
            'auto_initialize' => false,
        ]);
    }
}
