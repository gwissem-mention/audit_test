<?php

namespace HopitalNumerique\AideBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class AideType extends AbstractType
{
    private $_constraints = array();

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('route', 'text', array(
                'max_length' => $this->_constraints['route']['maxlength'],
                'required'   => true,
                'label'      => 'Route',
                'attr'       => array('class' => $this->_constraints['route']['class'])
            ))
            ->add('libelle', 'textarea', array(
                'required'   => true,
                'label'      => 'Libellé',
                'attr'     => array('class' => 'tinyMce')
            ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\AideBundle\Entity\Aide'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_aide_aide';
    }
}
