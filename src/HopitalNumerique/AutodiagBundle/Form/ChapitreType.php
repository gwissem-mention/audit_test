<?php

namespace HopitalNumerique\AutodiagBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class ChapitreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                'max_length' => 255, 
                'required'   => true, 
                'label'      => 'Titre',
                'label_attr' => array('class' => 'col-md-1 control-label')
            ))
            ->add('alias', 'text', array(
                'max_length' => 255,
                'required'   => false, 
                'label'      => 'Alias',
                'label_attr' => array('class' => 'col-md-2 control-label')
            ))
            ->add('noteOptimale', 'integer', array(
                'required'   => false, 
                'label'      => 'Note optimale',
                'label_attr' => array('class' => 'col-md-9 control-label')
            ))
            ->add('noteMinimale', 'integer', array(
                'required'   => false, 
                'label'      => 'Note minimale de déclenchement',
                'label_attr' => array('class' => 'col-md-9 control-label')
            ))
            ->add('synthese', 'textarea', array(
                'required'   => false, 
                'label'      => 'Phrase de synthèse',
                'label_attr' => array('class' => 'col-md-12'),
                'attr'       => array('rows' => 10)
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\AutodiagBundle\Entity\Chapitre'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_autodiag_chapitre';
    }
}
