<?php

namespace Nodevo\ContactBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

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
                'required' => true, 
                'label'    => 'Prenom',
                'attr'        => array(
                        'class' => $this->_constraints['prenom']['class'],
                        'placeholder' => 'Votre prénom'
                ),
            ))
            ->add('nom', 'text', array(
                'required' => true, 
                'label'    => 'Nom',
                'attr'        => array(
                        'class' => $this->_constraints['nom']['class'],
                        'placeholder' => 'Votre nom'
                ),
            ))
            ->add('mail', 'repeated', array(
                    'type'           => 'text',
                    'invalid_message' => 'Ces deux champs doivent être identiques.',
                    'required'       => true,
                    'first_options'  => array(
                            'label' => 'Adresse mail',
                            'attr' => array(
                                    'autocomplete' => 'off',
                                    'class' => $this->_constraints['mail']['class'],
                                    'placeholder' => 'Votre adresse mail'
                            )),
                    'second_options' => array(
                            'label' => 'Confirmer l\'adresse mail',
                            'attr' => array(
                                    'autocomplete' => 'off',
                                    'class' => $this->_constraints['mail']['class'],
                                    'placeholder' => 'Confirmation de votre adresse mail'
                            ))
            ))
            ->add('pays', 'text', array(
                    'required' => false,
                    'label'    => 'Pays',
                    'attr'        => array(
                            'class' => $this->_constraints['pays']['class'],
                            'placeholder' => 'Votre pays'
                    ),
            ))
            ->add('ville', 'text', array(
                    'required' => false,
                    'label'    => 'Ville',
                    'attr'        => array(
                            'class' => $this->_constraints['ville']['class'],
                            'placeholder' => 'Votre ville' 
                    ),
            ))
            ->add('telephone', 'text', array(
                    'required' => false,
                    'label'    => 'Téléphone',
                    'attr'        => array(
                            'class' => $this->_constraints['telephone']['class'], 
                            'data-mask' => $this->_constraints['telephone']['mask'],
                            'placeholder' => 'XX XX XX XX XX'
                    ),
            ))
            ->add('message', 'textarea', array(
                    'required' => true,
                    'label'    => 'Message',
                    'attr'        => array(
                            'class' => $this->_constraints['message']['class'],
                        'placeholder' => 'Votre prénom' 
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
