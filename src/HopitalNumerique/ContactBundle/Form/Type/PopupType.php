<?php
namespace HopitalNumerique\ContactBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Formulaire de contact de la popup.
 */
class PopupType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var array<string, string>
         */
        $destinataires = ( isset($options['destinataires']) ? $options['destinataires'] : array() );
        /**
         * @var string
         */
        $urlRedirection = ( isset($options['urlRedirection']) ? $options['urlRedirection'] : array() );

        $builder
            ->add('destinataires', 'hidden', array(
                'data' => $destinataires
            ))
            ->add('objet', 'text', array(
                'attr' => array(
                    'class' => 'validate[required]',
                    'maxlength' => 100
                )
            ))
            ->add('message', 'textarea', array(
                'attr' => array(
                    'class' => 'validate[required]',
                    'rows' => 10
                )
            ))
            ->add('urlRedirection', 'hidden', array(
                'data' => $urlRedirection
            ))
        ;
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setOptional(array('destinataires', 'urlRedirection'))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_contactbundle_popup';
    }
}
