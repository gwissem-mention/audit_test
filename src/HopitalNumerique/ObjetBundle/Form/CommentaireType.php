<?php

namespace HopitalNumerique\ObjetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateCreation', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'dateCreation'
            ))
            ->add('texte', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'texte'
            ))
            ->add('publier', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'publier'
            ))
            ->add('objet', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'objet'
            ))
            ->add('contenu', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'contenu'
            ))
            ->add('user', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'user'
            ))        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ObjetBundle\Entity\Commentaire'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_objet_commentaire';
    }
}
