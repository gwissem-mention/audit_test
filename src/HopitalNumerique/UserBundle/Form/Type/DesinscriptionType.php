<?php

/**
 * Formulaire de desinscription de l'utilisateur
 * 
 * @author Gaëtan MELCHILSEN
 * @copyright Nodevo
 */
namespace HopitalNumerique\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DesinscriptionType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class'      => 'HopitalNumerique\UserBundle\Entity\User',
                'csrf_protection' => false,
                'csrf_field_name' => '_token',
                // une clé unique pour aider à la génération du jeton secret
                'intention'       => 'task_item',
        ));
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
        $builder->add('raisonDesinscription', 'textarea', array(
                    'required' => false,
                    'label'    => 'Quelle est la raison de votre désinscription ?',
                    'attr'        => array(
                            'rows'   => 10
                    ),
            ))
        ->add('termsAccepted', 'checkbox', array(
                'required'   => true,
                'label'      => 'Je veux me désinscrire',
                'label_attr' => array('class' => 'confirmation'),
                'attr'       => array('class' => 'validate[required] checkbox')
        ));                
    }

    /**
     * Retourne le nom du formulaire
     * @return string Nom du formulaire
     */
    public function getName()
    {
        return 'nodevo_user_desinscription';
    }
}