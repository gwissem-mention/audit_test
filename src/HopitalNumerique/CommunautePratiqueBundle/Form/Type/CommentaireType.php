<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Formulaire d'édition d'un commentaire de la communauté de pratique.
 */
class CommentaireType extends \Symfony\Component\Form\AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', 'textarea', array(
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
            'data_class' => 'HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_communautepratiquebundle_commentaire';
    }
}
