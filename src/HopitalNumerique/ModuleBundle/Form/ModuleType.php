<?php

namespace HopitalNumerique\ModuleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

/**
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ModuleType extends AbstractType
{
    private $_constraints = array();
    
    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', 'text', array(
                    'max_length' => $this->_constraints['titre']['maxlength'],
                    'required' => true,
                    'label'    => 'Titre du module',
                    'attr'        => array(
                        'class' => $this->_constraints['titre']['class']
                    ),
            ))
            ->add('productions', 'genemu_jqueryselect2_entity', array(
                    'class'         => 'HopitalNumeriqueObjetBundle:Objet',
                    'property'      => 'titre',
                    'multiple'      => true,
                    'required'      => true,
                    'label'         => 'Productions concernées',
                    'empty_value'   => ' - ',
                    'attr'          => array('class' => 'productions'),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->getProductionsActive();
                    }
            ))
            ->add('connaissances', 'genemu_jqueryselect2_entity', array(
                    'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property'      => 'libelle',
                    'multiple'      => true,
                    'required'      => true,
                    'label'         => 'Connaissances concernées',
                    'empty_value'   => ' - ',
                    'attr'          => array('class' => 'connaissances'),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                            ->where('ref.code = :etat')
                            ->setParameter('etat', 'DEPARTEMENT')
                            ->orderBy('ref.order', 'ASC');
                    }
            ))
            ->add('duree', 'entity', array(
                    'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property'      => 'libelle',
                    'required'      => false,
                    'label'         => 'Durée',
                    'empty_value'   => ' - ',
                    'attr'          => array(),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                        ->where('ref.code = :etat')
                        ->setParameter('etat', 'DUREE_FORMATION')
                        ->orderBy('ref.order', 'ASC');
                    }
            ))
            ->add('horairesType', 'text', array(
                    'max_length' => $this->_constraints['horairesType']['maxlength'],
                    'required' => false,
                    'label'    => 'Horaires type',
                    'attr'        => array(
                            'class' => $this->_constraints['horairesType']['class']
                    ),
            ))
            ->add('lieu', 'textarea', array(
                    'required' => false,
                    'label'    => 'Lieu',
                    'attr'        => array(
                            'rows'   => 3
                    ),
            ))
            ->add('description', 'textarea', array(
                    'required' => false,
                    'label'    => 'Description',
                    'attr'        => array(
                            'rows'   => 3
                    ),
            ))
            ->add('nombrePlaceDisponible', 'integer', array(
                    'required'   => false, 
                    'label'      => 'Nombre de places disponibles',
                    'attr'        => array(
                            'class' => $this->_constraints['nombrePlaceDisponible']['class']
                    )
            ))
            ->add('prerequis', 'textarea', array(
                    'required' => false,
                    'label'    => 'Prérequis',
                    'attr'        => array(
                            'rows'   => 3
                    ),
            ))
            ->add('formateur', 'genemu_jqueryselect2_entity', array(
                    'class'         => 'HopitalNumeriqueUserBundle:User',
                    'property'      => 'appellation',
                    'multiple'      => false,
                    'required'      => false,
                    'label'         => 'Formateur',
                    'empty_value'   => ' - ',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('user')
                            ->where('user.enabled = ' . 1)
                            ->orderBy('user.nom', 'ASC');
                    }
            ))
            ->add('textMailRappel', 'textarea', array(
                    'required' => false,
                    'label'    => 'Texte du mail de rappel',
                    'attr'        => array(
                            'rows'   => 3
                    ),
            ))
            ->add('file', 'file', array(
                    'required' => false, 
                    'label'    => 'Pièce-jointe'
            ))
            ->add('path', 'hidden')
            ->add('statut', 'entity', array(
                    'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property'      => 'libelle',
                    'required'      => true,
                    'label'         => 'Statut',
                    'empty_value'   => ' - ',
                    'attr'          => array('class' => $this->_constraints['statut']['class'] ),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                        ->where('ref.code = :etat')
                        ->setParameter('etat', 'ETAT')
                        ->orderBy('ref.order', 'ASC');
                    }
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ModuleBundle\Entity\Module'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_module_module';
    }
}
