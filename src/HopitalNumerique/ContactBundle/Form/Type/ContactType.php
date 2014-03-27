<?php

namespace HopitalNumerique\ContactBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Nodevo\ContactBundle\Form\Type\ContactType as NodevoContactType;

class ContactType extends NodevoContactType
{
    private $_constraints = array();

    public function __construct($manager, $validator)
    {
        parent::__construct($manager, $validator);

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


        /*echo '‹pre›';
        \Doctrine\Common\Util\Debug::dump($this->_constraints);
        die();*/
        parent::buildForm($builder, $options);
        
        $builder
        ->add('fonctionStructure', 'text', array(
                    'max_length' => $this->_constraints['fonctionStructure']['maxlength'],
                    'required'   => false,
                    'label'      => 'Fonction dans l\'établissement',
                    'attr'       => array('class' => $this->_constraints['fonctionStructure']['class'])
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ContactBundle\Entity\Contact'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopital_numerique_contact_contact';
    }
}
