<?php

namespace HopitalNumerique\AutodiagBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'Titre'
            ))
            ->add('note', 'integer', array(
                'max_length' => 3, 
                'required'   => false, 
                'label'      => 'Note optimal'
            ))
            ->add('affichageRestitution', 'checkbox', array(
                'required'   => false, 
                'label'      => 'Afficher lors de la réstitution ?'
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\AutodiagBundle\Entity\Categorie'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_autodiag_categorie';
    }
}
