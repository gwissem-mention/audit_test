<?php

namespace HopitalNumerique\ReportBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReportType extends AbstractType
{
    private $_constraints = array();

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
    }

        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', 'text', array(
                        'required'   => true,
                        'label'      => 'Url',
                        'attr'       => array(
                            'class'      => $this->_constraints['observations']['class'],
                            'readonly'   => 'readonly'
                            )
            ))
            ->add('observations', 'textarea', array(
                        'required'   => true,
                        'label'      => 'Observations',
                        'attr'       => array(
                            'class'      => $this->_constraints['observations']['class'],
                            'rows'       => 10,
                            )
            ))
            
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ReportBundle\Entity\Report'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_reportbundle_report';
    }
}
