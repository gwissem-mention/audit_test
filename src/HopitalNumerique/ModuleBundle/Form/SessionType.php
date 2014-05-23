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
class SessionType extends AbstractType
{
    private $_constraints = array();
    
    public function __construct($manager, $validator)
    {
        $this->_constraints = $manager->getConstraints( $validator );
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateSession', 'genemu_jquerydate', array(
                'required' => true, 
                'label'    => 'Date de la session',
                'widget'   => 'single_text',
                'attr'     => array('class' => $this->_constraints['dateSession']['class'] )
            ))
            ->add('dateOuvertureInscription', 'genemu_jquerydate', array(
                'required' => true, 
                'label'    => 'Date d\'ouverture des inscriptions',
                'widget'   => 'single_text',
                'attr'     => array('class' => $this->_constraints['dateOuvertureInscription']['class'] )
            ))
            ->add('dateFermetureInscription', 'genemu_jquerydate', array(
                'required' => true, 
                'label'    => 'Date de fermeture des inscriptions',
                'widget'   => 'single_text',
                'attr'     => array('class' => $this->_constraints['dateFermetureInscription']['class'] )
            ))
            ->add('duree', 'entity', array(
                    'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property'      => 'libelle',
                    'required'      => true,
                    'label'         => 'Durée',
                    'empty_value'   => ' - ',
                    'attr'          => array('class' => $this->_constraints['duree']['class'] ),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                        ->where('ref.code = :etat')
                        ->setParameter('etat', 'DUREE_FORMATION')
                        ->orderBy('ref.order', 'ASC');
                    }
            ))
            ->add('horaires', 'textarea', array(
                    'required' => true,
                    'label'    => 'Horaires',
                    'attr'        => array(
                            'rows'   => 2,
                            'class' => $this->_constraints['horaires']['class']
                    ),
            ))
            ->add('lieu', 'textarea', array(
                    'required' => true,
                    'label'    => 'Lieu',
                    'attr'        => array(
                            'rows'   => 2,
                            'class' => $this->_constraints['lieu']['class']
                    ),
            ))
            ->add('description', 'textarea', array(
                    'required' => true,
                    'label'    => 'Description',
                    'attr'        => array(
                            'rows'   => 3,
                            'class' => $this->_constraints['description']['class']
                    ),
            ))
            ->add('nombrePlaceDisponible', 'text', array(
                'max_length' => 255, 
                'required'   => false, 
                'label'      => 'Nombre de places disponibles',
                'attr'       => array('class' => $this->_constraints['nombrePlaceDisponible']['class'] )
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
            ->add('restrictionAcces', 'genemu_jqueryselect2_entity', array(
                    'class'         => 'NodevoRoleBundle:Role',
                    'property'      => 'name',
                    'multiple'      => true,
                    'required'      => false,
                    'label'         => 'Autoriser ce module à',
                    'empty_value'   => ' - ',
                    'attr'          => array('class' => 'restriction-acces'),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('role')
                        ->where('role.etat = :actif')
                        ->setParameter('actif', 3)
                        ->orderBy('role.name', 'ASC');
                    }
            ))
            ->add('textMailRappel', 'textarea', array(
                    'required' => true,
                    'label'    => 'Texte du mail de rappel',
                    'attr'        => array(
                            'rows'   => 2,
                            'class' => $this->_constraints['textMailRappel']['class']
                    ),
            ))
            ->add('file', 'file', array(
                    'required' => false, 
                    'label'    => 'Fiche de présence'
            ))
            ->add('path', 'hidden')
            ->add('etat', 'entity', array(
                    'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property'      => 'libelle',
                    'required'      => true,
                    'label'         => 'Etat',
                    'empty_value'   => ' - ',
                    'attr'          => array('class' => $this->_constraints['etat']['class'] ),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                        ->where('ref.code = :etat')
                        ->setParameter('etat', 'STATUT_SESSION_FORMATION')
                        ->orderBy('ref.order', 'ASC');
                    }
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ModuleBundle\Entity\Session'
        ));
    }

    public function getName()
    {
        return 'hopitalnumerique_module_session';
    }
}
