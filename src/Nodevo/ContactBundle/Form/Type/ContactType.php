<?php

namespace Nodevo\ContactBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class ContactType extends AbstractType
{
    private $_constraints = array();

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
    }

    /**
     * Ajout des éléments dans le formulaire, spécifie les labels, les widgets utilisés ainsi que l'obligation
     * 
     * @param  FormBuilderInterface $builder Le builder contient les champs du formulaire
     * @param  array                $options Data passée au formulaire
     * 
     * @return void                 
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prenom', 'text', array(
                'required' => true, 
                'label'    => 'Prenom',
                'attr'        => array('class' => $this->_constraints['prenom']['class'] ),
            ))
            ->add('nom', 'text', array(
                'required' => true, 
                'label'    => 'Nom',
                'attr'        => array('class' => $this->_constraints['nom']['class'] ),
            ))
            ->add('mail', 'text', array(
                'required' => true, 
                'label'    => 'Adresse mail',
                'attr'        => array('class' => $this->_constraints['mail']['class'] ),
            ))
            
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nodevo\ContactBundle\Entity\Contact'
        ));
    }

    public function getName()
    {
        return 'nodevo_contact_contact';
    }
}
