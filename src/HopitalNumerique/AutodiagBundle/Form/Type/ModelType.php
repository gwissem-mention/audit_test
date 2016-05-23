<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ModelType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('instructions', null, [
                'attr' => [
                    'class' => 'tinyMce'
                ]
            ])
            ->add('partialResultsAuthorized')
            ->add('synthesisAuthorized')
            ->add('domaines')
            ->add('questionnaire')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\AutodiagBundle\Entity\Model'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_autodiagbundle_model';
    }
}
