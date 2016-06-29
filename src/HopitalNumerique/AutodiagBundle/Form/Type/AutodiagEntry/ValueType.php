<?php
namespace HopitalNumerique\AutodiagBundle\Form\Type\AutodiagEntry;

use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry\Value;
use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValueType extends AbstractType
{
    protected $attributeBuilderProvider;

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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Value $data */
            $data = $event->getData();
            $form = $event->getForm();

            $attributeBuilder = $this->attributeBuilderProvider->getBuilder($data->getAttribute()->getType());
            $form
                ->add('value', $attributeBuilder->getFormType(), [
                    'attribute_builder' => $attributeBuilder
                ])
                ->add('description', TextareaType::class)
            ;
        });

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry\Value',
            'label_format' => 'ad.autodiag.%name%'
        ));
    }
}
