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
            ->add('texte', 'textarea', array(
                'required'   => true, 
                'label'      => 'Texte du commentaire',
                'attr'       => array(
                    'rows' => 8
                )
            ))
            ->add('publier', 'checkbox', array(
                'label'      => 'Publier ?'
            ))      ;
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
