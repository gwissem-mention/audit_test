<?php

/**
 * Formulaire d'édition du mot de passe de l'utilisateur.
 *
 * @author Gaëtan MELCHILSEN
 * @copyright Nodevo
 */

namespace HopitalNumerique\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MotDePasseType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
                'data_class' => 'HopitalNumerique\UserBundle\Entity\User',
                'csrf_protection' => false,
                'csrf_field_name' => '_token',
                // une clé unique pour aider à la génération du jeton secret
                'intention' => 'task_item',
        ]);
    }

    /**
     * Ajout des éléments dans le formulaire, spécifie les labels, les widgets utilisés ainsi que l'obligation.
     *
     * @param FormBuilderInterface $builder Le builder contient les champs du formulaire
     * @param array                $options Data passée au formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('oldPassword', 'password', [
                        'mapped' => false,
                        'label' => 'Ancien mot de passe',
                        'required' => true,
                        'attr' => [
                                'autocomplete' => 'off',
                                'class' => 'validate[required]',
                        ],
        ]);
        $builder->add('plainPassword', 'repeated', [
                        'type' => 'password',
                        'invalid_message' => 'Ces deux champs doivent être identiques.',
                        'required' => true,
                        'first_options' => ['label' => 'Mot de passe', 'attr' => ['autocomplete' => 'off', 'class' => 'validate[required]']],
                        'second_options' => ['label' => 'Confirmer le mot de passe', 'attr' => ['autocomplete' => 'off', 'class' => 'validate[required, equals[nodevo_user_motdepasse_plainPassword_first]]']],
                ]);
    }

    /**
     * Retourne le nom du formulaire.
     *
     * @return string Nom du formulaire
     */
    public function getName()
    {
        return 'nodevo_user_motdepasse';
    }
}
