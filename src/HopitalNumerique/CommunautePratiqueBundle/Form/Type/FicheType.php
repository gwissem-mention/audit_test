<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Formulaire d'édition d'une fiche de la communauté de pratique.
 */
class FicheType extends \Symfony\Component\Form\AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('questionPosee', 'text', array(
                'label' => 'Question posée',
                'required' => true,
                'attr' => array(
                    'class' => 'validate[required]',
                    'maxlength' => 255
                )
            ))
            ->add('contexte', 'textarea', array(
                'label' => 'Éléments de contexte à prendre en compte',
                'required' => true,
                'attr' => array(
                    'class' => 'validate[required]',
                    'rows' => 4
                )
            ))
            ->add('description', 'textarea', array(
                'label' => 'Description complète du problème',
                'required' => true,
                'attr' => array(
                    'class' => 'validate[required]',
                    'rows' => 4
                )
            ))
            ->add('aideAttendue', 'textarea', array(
                'label' => 'Aide attendue',
                'required' => true,
                'attr' => array(
                    'class' => 'validate[required]',
                    'rows' => 4
                )
            ))
            ->add('resume', 'textarea', array(
                'label' => 'En résumé...',
                'required' => true,
                'attr' => array(
                    'class' => 'validate[required]',
                    'rows' => 4
                )
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_communautepratiquebundle_fiche';
    }
}
