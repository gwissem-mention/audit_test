<?php
namespace HopitalNumerique\ContactBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Formulaire de contact de la popup.
 */
class PopupInviteType extends AbstractType
{

    /**
     * Constructeur.
     */
    public function __construct()
    {
    }


    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var string
         */
        $urlRedirection = ( isset($options['urlRedirection']) ? $options['urlRedirection'] : array() );

        $builder
            ->add('destinataires', 'text', array(
                'label' => 'E-mail des utilisateurs (Séparer les adresses par des virgules) *',
                'attr' => array(
                    'class' => 'validate[required]',
                    'maxlength' => 250
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
            ->setOptional(array('urlRedirection'))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_contactbundle_popup_invite';
    }
}
