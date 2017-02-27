<?php

namespace HopitalNumerique\ContactBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var string
         */
        $urlRedirection = (isset($options['urlRedirection']) ? $options['urlRedirection'] : []);

        $builder
            ->add('destinataires', 'text', [
                'label' => 'E-mail des utilisateurs (Séparer les adresses par des virgules)',
                'attr' => [
                    'class' => 'validate[required]',
                    'maxlength' => 250,
                ],
            ])
            ->add('urlRedirection', 'hidden', [
                'data' => $urlRedirection,
            ])
            ->add('idGroupe', 'hidden', [
                'data' => $options['idGroupe'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'idGroupe' => 0,
            ])
            ->setOptional(['urlRedirection'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_contactbundle_popup_invite';
    }
}
