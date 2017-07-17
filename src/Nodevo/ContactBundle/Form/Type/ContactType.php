<?php

namespace Nodevo\ContactBundle\Form\Type;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ContactType extends AbstractType
{
    private $_constraints = [];
    protected $_securityContext;

    public function __construct($manager, $validator, $securityContext)
    {
        $this->_constraints = $manager->getConstraints($validator);
        $this->_securityContext = $securityContext;
    }

    /**
     * Ajout des éléments dans le formulaire, spécifie les labels, les widgets utilisés ainsi que l'obligation.
     *
     * @param FormBuilderInterface $builder Le builder contient les champs du formulaire
     * @param array                $options Data passée au formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->_securityContext->getToken()->getUser();

        $builder
            ->add('prenom', 'text', [
                        'max_length' => $this->_constraints['prenom']['maxlength'],
                        'required' => true,
                        'label' => 'Prénom',
                        'attr' => [
                        'class' => $this->_constraints['prenom']['class'],
                        ],
                        'data' => ($user instanceof User && !is_null($user->getFirstname())) ? $user->getFirstname() : '',
            ])
            ->add('nom', 'text', [
                        'max_length' => $this->_constraints['nom']['maxlength'],
                        'required' => true,
                        'label' => 'Nom',
                        'attr' => [
                        'class' => $this->_constraints['nom']['class'],
                        ],
                        'data' => ($user instanceof User && !is_null($user->getLastname())) ? $user->getLastname() : '',
            ])
            ->add('mail', 'repeated', [
                    'type' => 'text',
                    'invalid_message' => 'Ces deux champs doivent être identiques.',
                    'required' => true,
                    'first_options' => [
                            'label' => 'Adresse email',
                            'max_length' => $this->_constraints['mail']['maxlength'],
                            'attr' => [
                                    'autocomplete' => 'off',
                                    'class' => $this->_constraints['mail']['class'],
                            ],
                            'data' => ($user instanceof User && !is_null($user->getEmail())) ? $user->getEmail() : '',
                            ],
                    'second_options' => [
                            'label' => 'Confirmer l\'adresse email',
                            'max_length' => $this->_constraints['mail']['maxlength'],
                            'attr' => [
                                    'autocomplete' => 'off',
                                    'class' => $this->_constraints['mail']['class'] . 'validate[equals[hopital_numerique_contact_contact_mail_first]]',
                            ],
                            'data' => ($user instanceof User && !is_null($user->getEmail())) ? $user->getEmail() : '',
                            ],
            ])
            ->add('telephone', 'text', [
                    'max_length' => $this->_constraints['telephone']['maxlength'],
                    'required' => false,
                    'label' => 'Téléphone',
                    'attr' => [
                            'class' => $this->_constraints['telephone']['class'],
                            'data-mask' => $this->_constraints['telephone']['mask'],
                    ],
                    'data' => ($user instanceof User && !is_null($user->getPhoneNumber())) ? $user->getPhoneNumber() : '',
            ])
            ->add('message', 'textarea', [
                    'required' => true,
                    'label' => 'Votre message',
                    'attr' => [
                            'class' => $this->_constraints['message']['class'],
                            'rows' => 8,
                    ],
            ])

            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Nodevo\ContactBundle\Entity\Contact',
        ]);
    }

    public function getName()
    {
        return 'nodevo_contact_contact';
    }
}
