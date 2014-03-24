<?php

namespace Nodevo\MenuBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MenuType extends AbstractType
{
    private $_constraints = array();

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'max_length' => $this->_constraints['name']['maxlength'], 
                'required'   => true, 
                'label'      => 'Nom',
                'attr'       => array('class' => $this->_constraints['name']['class'] )
            ));

        if( ! $options['data']->getId() ) {
            $builder->add('alias', 'text', array(
                'max_length' => $this->_constraints['name']['maxlength'], 
                'required'   => true, 
                'label'      => 'Alias',
                'attr'       => array('class' => $this->_constraints['alias']['class'] )
            ));
        }else{
            $builder->add('alias', 'text', array(
                'required'   => true, 
                'label'      => 'Alias',
                'attr'       => array('readonly' => 'readonly' )
            ));
        }

        $builder
            ->add('cssClass', 'text', array(
                'max_length' => $this->_constraints['cssClass']['maxlength'], 
                'required'   => false, 
                'label'      => 'Classe CSS',
                'attr'       => array('class' => $this->_constraints['cssClass']['class'] )
            ))
            ->add('cssId', 'text', array(
                'max_length' => $this->_constraints['cssId']['maxlength'], 
                'required'   => false, 
                'label'      => 'ID CSS',
                'attr'       => array('class' => $this->_constraints['cssId']['class'] )
            ));
    }

    public function getName()
    {
        return 'nodevo_menu_menu';
    }
}