<?php

namespace HopitalNumerique\ContactBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
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
        parent::buildForm($builder, $options);
        
        $builder
        ->add('fonctionStructure', 'text', array(
                'max_length' => $this->_constraints['fonctionStructure']['maxlength'],
                'required'   => false,
                'label'      => 'Fonction dans l\'établissement',
                'attr'       => array(
                        'class' => $this->_constraints['fonctionStructure']['class'],
                        'placeholder' => 'Votre fonction dans l\'établissement'
                )
        ))

        ->add('civilite', 'entity', array(
                'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'      => 'libelle',
                'required'      => true,
                'label'         => 'Civilite',
                'empty_value'   => ' - ',
                'attr'          => array('class' => $this->_constraints['civilite']['class'] ),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                        ->where('ref.code = :etat')
                        ->setParameter('etat', 'CIVILITE')
                        ->orderBy('ref.order', 'ASC');
                }
        ))
        ->add('codepostal', 'text', array(
            'required' => false, 
            'label'    => 'Code Postal',
            'attr'        => array(
                    'class' => $this->_constraints['codepostal']['class'],
                    'placeholder' => 'Votre Code Postal'
            ),
        ))
        
        ->add('region', 'entity', array(
                'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'    => 'libelle',
                'required'    => false,
                'label'       => 'Région',
                'empty_value' => ' - ',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                    ->where('ref.code = :etat')
                    ->setParameter('etat', 'REGION')
                    ->orderBy('ref.order', 'ASC');
                }
        ))
        
        ->add('departement', 'entity', array(
                'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'    => 'libelle',
                'required'    => false,
                'label'       => 'Département',
                'empty_value' => ' - ',
                'attr'        => array(),
                'query_builder' => function(EntityRepository $er) use($options) {
                    return $er->createQueryBuilder('ref')
                    ->where('ref.code = :etat')
                    ->setParameter('etat', 'DEPARTEMENT')
                    ->orderBy('ref.libelle', 'ASC');
                }
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
