<?php

namespace HopitalNumerique\AideBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AideType extends AbstractType
{
    private $_constraints = [];

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints($validator);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('route', 'text', [
                'max_length' => $this->_constraints['route']['maxlength'],
                'required' => true,
                'label' => 'Route',
                'attr' => ['class' => $this->_constraints['route']['class']],
            ])
            ->add('libelle', 'textarea', [
                'required' => true,
                'label' => 'Libellé',
                'attr' => ['class' => 'tinyMce'],
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\AideBundle\Entity\Aide',
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_aide_aide';
    }
}
