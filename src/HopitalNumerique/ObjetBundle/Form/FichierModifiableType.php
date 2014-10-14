<?php

namespace HopitalNumerique\ObjetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FichierModifiableType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('referentAnap', 'text', array(
                'max_length' => 255,
                'required'   => false, 
                'label'      => 'Réferent Anap',
                'attr'       => array('class' => 'validate[minsize[1],maxsize[255]]' )
            ))

            ->add('sourceDocument', 'text', array(
                'max_length' => 255,
                'required'   => false, 
                'label'      => 'Source du document',
                'attr'       => array('class' => 'validate[minsize[1],maxsize[255]]' )
            ))

            ->add('commentaires', 'textarea', array(
                'required'   => false, 
                'label'      => 'Commentaire',
                'attr'       => array('rows' => 3)
            ))

            ->add('fileEdit', 'file', array(
                'required' => false, 
                'label'    => 'Fichier Editable'
            ))
            ->add('pathEdit', 'hidden')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ObjetBundle\Entity\FichierModifiable'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_objet_fichiermodifiable';
    }
}
