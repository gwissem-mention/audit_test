<?php

namespace Nodevo\FaqBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class FaqType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('question', 'textarea', array(
                'required'   => true, 
                'label'      => 'Question'
            ))
            ->add('reponse', 'textarea', array(
                'required'   => true, 
                'label'      => 'Réponse'
            ))
            ->add('categorie', 'genemu_jqueryselect2_entity', array(
                'class'         => 'NodevoFaqBundle:Categorie',
                'property'      => 'name',
                'required'      => true,
                'label'         => 'Catégorie',
                'empty_value'   => ' - ',
                'attr'          => array( 'placeholder' => 'Selectionnez la catégorie correspondante' )
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nodevo\FaqBundle\Entity\Faq'
        ));
    }

    public function getName()
    {
        return 'nodevo_faq_faq';
    }
}
