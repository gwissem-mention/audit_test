<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type\Model;

use HopitalNumerique\AutodiagBundle\Entity\Model\Preset;
use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderProvider;
use HopitalNumerique\AutodiagBundle\Service\Attribute\PresetableAttributeBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Field used to dynamically render collection of Preset, by the attributeBuilderProvider
 *
 * @package HopitalNumerique\AutodiagBundle\Form\Type\Model
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class PresetType extends AbstractType
{
    /** @var AttributeBuilderProvider */
    private $attributeBuilderProvider;

    public function __construct(AttributeBuilderProvider $attributeBuilderProvider)
    {
        $this->attributeBuilderProvider = $attributeBuilderProvider;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $attributeBuilder = $this->attributeBuilderProvider->getBuilder($data->getType());
                if ($attributeBuilder instanceof PresetableAttributeBuilderInterface) {
                    $form->add(
                        $attributeBuilder->getPresetForm()
                    );
                }
            }
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\AutodiagBundle\Entity\Model\Preset',
            'label_format' => 'ad.model.preset.%name%'
        ));
    }
}
