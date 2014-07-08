<?php

namespace Nodevo\ContactBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ContactType extends AbstractType
{
    private $_constraints = array();
    protected $_securityContext;

    public function __construct($manager, $validator, $securityContext)
    {
        $this->_constraints     = $manager->getConstraints( $validator );
        $this->_securityContext = $securityContext;
    }

    /**
     * Ajout des éléments dans le formulaire, spécifie les labels, les widgets utilisés ainsi que l'obligation
     * 
     * @param  FormBuilderInterface $builder Le builder contient les champs du formulaire
     * @param  array                $options Data passée au formulaire
     * 
     * @return void                 
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->_securityContext->getToken()->getUser();

        $builder
            ->add('prenom', 'text', array(
                        'max_length' => $this->_constraints['prenom']['maxlength'],
                        'required'   => true, 
                        'label'      => 'Prénom',
                        'attr'       => array(
                        'class'      => $this->_constraints['prenom']['class']
                        ),
                        'data'       => ('anon.' != $user && !is_null($user->getPrenom())) ? $user->getPrenom() : ''
            ))
            ->add('nom', 'text', array(
                        'max_length' => $this->_constraints['nom']['maxlength'],
                        'required'   => true, 
                        'label'      => 'Nom',
                        'attr'       => array(
                        'class'      => $this->_constraints['nom']['class']
                        ),
                        'data'       => ('anon.' != $user && !is_null($user->getNom())) ? $user->getNom() : ''
            ))
            ->add('mail', 'repeated', array(
                    'type'           => 'text',
                    'invalid_message' => 'Ces deux champs doivent être identiques.',
                    'required'       => true,
                    'first_options'  => array(
                            'label' => 'Adresse email',
                            'max_length' => $this->_constraints['mail']['maxlength'],
                            'attr' => array(
                                    'autocomplete' => 'off',
                                    'class' => $this->_constraints['mail']['class']
                            ),
                            'data'       => ('anon.' != $user && !is_null($user->getEmail())) ? $user->getEmail() : ''
                            ),
                    'second_options' => array(
                            'label' => 'Confirmer l\'adresse email',
                            'max_length' => $this->_constraints['mail']['maxlength'],
                            'attr' => array(
                                    'autocomplete' => 'off',
                                    'class' => $this->_constraints['mail']['class'] . 'validate[equals[hopital_numerique_contact_contact_mail_first]]'
                            ),
                            'data'       => ('anon.' != $user && !is_null($user->getEmail())) ? $user->getEmail() : ''
                            ),
            ))
            ->add('telephone', 'text', array(
                    'max_length' => $this->_constraints['telephone']['maxlength'],
                    'required' => false,
                    'label'    => 'Téléphone',
                    'attr'        => array(
                            'class' => $this->_constraints['telephone']['class'], 
                            'data-mask' => $this->_constraints['telephone']['mask']
                    ),
                    'data'       => ('anon.' != $user && !is_null($user->getTelephoneDirect())) ? $user->getTelephoneDirect() : ''
            ))
            ->add('message', 'textarea', array(
                    'required' => true,
                    'label'    => 'Votre message',
                    'attr'        => array(
                            'class' => $this->_constraints['message']['class'],
                            'rows'   => 8
                    )
            ))
            
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nodevo\ContactBundle\Entity\Contact'
        ));
    }

    public function getName()
    {
        return 'nodevo_contact_contact';
    }
}
