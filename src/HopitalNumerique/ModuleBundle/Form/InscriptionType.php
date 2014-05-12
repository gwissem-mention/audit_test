<?php

namespace HopitalNumerique\ModuleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

/**
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class InscriptionType extends AbstractType
{
    private $_constraints = array();
    
    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('commentaire', 'textarea', array(
                    'required' => false,
                    'label'    => 'Informations complémentaires',
                    'attr'     => array(
                        'rows'   => 8
                    )
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ModuleBundle\Entity\Inscription'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_module_inscription';
    }
}