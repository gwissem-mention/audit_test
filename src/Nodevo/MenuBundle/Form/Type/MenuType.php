<?php

namespace Nodevo\MenuBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MenuType extends AbstractType
{
    private $_constraints = [];

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints($validator);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'max_length' => $this->_constraints['name']['maxlength'],
                'required' => true,
                'label' => 'Nom',
                'attr' => ['class' => $this->_constraints['name']['class']],
            ])
            ->add('alias', 'text', [
                'max_length' => $this->_constraints['name']['maxlength'],
                'required' => true,
                'label' => 'Alias',
                'attr' => ['class' => $this->_constraints['alias']['class']],
            ])

            ->add('cssClass', 'text', [
                'max_length' => $this->_constraints['cssClass']['maxlength'],
                'required' => false,
                'label' => 'Classe CSS',
                'attr' => ['class' => $this->_constraints['cssClass']['class']],
            ])
            ->add('cssId', 'text', [
                'max_length' => $this->_constraints['cssId']['maxlength'],
                'required' => false,
                'label' => 'ID CSS',
                'attr' => ['class' => $this->_constraints['cssId']['class']],
            ]);
    }

    public function getName()
    {
        return 'nodevo_menu_menu';
    }
}
