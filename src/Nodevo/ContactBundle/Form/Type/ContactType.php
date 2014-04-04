<?php

namespace Nodevo\ContactBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContactType extends AbstractType
{
    private $_constraints = array();

    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
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
        $builder
            ->add('prenom', 'text', array(
                'max_length' => $this->_constraints['prenom']['maxlength'],
                'required' => true, 
                'label'    => 'Prenom',
                'attr'        => array(
                        'class' => $this->_constraints['prenom']['class']
                ),
            ))
            ->add('nom', 'text', array(
                'max_length' => $this->_constraints['nom']['maxlength'],
                'required' => true, 
                'label'    => 'Nom',
                'attr'        => array(
                        'class' => $this->_constraints['nom']['class']
                ),
            ))
            ->add('mail', 'repeated', array(
                    'type'           => 'text',
                    'invalid_message' => 'Ces deux champs doivent être identiques.',
                    'required'       => true,
                    'first_options'  => array(
                            'label' => 'Adresse mail',
                            'max_length' => $this->_constraints['mail']['maxlength'],
                            'attr' => array(
                                    'autocomplete' => 'off',
                                    'class' => $this->_constraints['mail']['class']
                            )),
                    'second_options' => array(
                            'label' => 'Confirmer l\'adresse mail',
                            'max_length' => $this->_constraints['mail']['maxlength'],
                            'attr' => array(
                                    'autocomplete' => 'off',
                                    'class' => $this->_constraints['mail']['class'] . 'validate[equals[hopital_numerique_contact_contact_mail_first]]'
                            ))
            ))
            ->add('ville', 'text', array(
                    'max_length' => $this->_constraints['ville']['maxlength'],
                    'required' => false,
                    'label'    => 'Ville',
                    'attr'        => array(
                            'class' => $this->_constraints['ville']['class']
                    ),
            ))
            ->add('codepostal', 'text', array(
                'max_length' => $this->_constraints['codepostal']['maxlength'],
                'required' => false, 
                'label'    => 'Code Postal',
                'attr'        => array(
                        'class' => $this->_constraints['codepostal']['class']
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
            ))
            ->add('message', 'textarea', array(
                    'required' => true,
                    'label'    => 'Message',
                    'attr'        => array(
                            'class' => $this->_constraints['message']['class']
                    ),
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
