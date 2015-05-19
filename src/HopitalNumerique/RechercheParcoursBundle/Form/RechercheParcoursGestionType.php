<?php

namespace HopitalNumerique\RechercheParcoursBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class RechercheParcoursGestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'nom'
            ))
            ->add('domaines', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'domaines'
            ))        
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_rechercheparcours_rechercheparcoursgestion';
    }
}
