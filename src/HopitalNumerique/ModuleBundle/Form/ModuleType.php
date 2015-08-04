<?php

namespace HopitalNumerique\ModuleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use HopitalNumerique\UserBundle\Manager\UserManager;

/**
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ModuleType extends AbstractType
{
    private $_constraints = array();
    private $_userManager;
    
    public function __construct($manager, $validator, UserManager $userManager)
    {
        $this->_constraints = $manager->getConstraints( $validator );

        $this->_userManager = $userManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $connectedUser = $this->_userManager->getUserConnected();

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
                    'required'      => false,
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
                    'required'      => false,
                    'group_by'      => 'parentName',
                    'label'         => 'Connaissances concernées',
                    'empty_value'   => ' - ',
                    'attr'          => array('class' => 'connaissances'),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                            ->where('ref.code = :etat')
                            ->setParameter('etat', 'CONNAISSANCES_AMBASSADEUR_SI')
                            ->leftJoin('ref.parent', 'parent')
                            ->orderBy('parent.libelle', 'ASC')
                            ->addOrderBy('ref.order', 'ASC');
                    }
            ))
            ->add('domaines', 'entity', array(
                'class'       => 'HopitalNumeriqueDomaineBundle:Domaine',
                'property'    => 'nom',
                'required'    => false,
                'multiple'    => true,
                'label'       => 'Domaine(s) associé(s)',
                'empty_value' => ' - ',
                'query_builder' => function(EntityRepository $er) use ($connectedUser){
                    return $er->getDomainesUserConnectedForForm($connectedUser->getId());
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
                    'query_builder' => function(EntityRepository $er) use ($connectedUser){
                        return $er->createQueryBuilder('user')
                            ->leftJoin('user.domaines', 'domaine')
                            ->where('domaine.id IN (:domainesIds)')
                                ->setParameter('domainesIds', $connectedUser->getDomainesId())
                            ->andWhere('user.enabled = ' . 1)
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

            ->add('mailAccuseInscription', 'checkbox', array(
                'required'   => false, 
                'label'      => 'Envoyer le mail d\'accusé de réception d\'inscription ?'
            ))

            ->add('mailConfirmationInscription', 'checkbox', array(
                'required'   => false, 
                'label'      => 'Envoyer le mail de confirmation d\'inscription ?'
            ))

            ->add('mailRefusInscription', 'checkbox', array(
                'required'   => false, 
                'label'      => 'Envoyer le mail de refus ?'
            ))

            ->add('mailRappelEvalution', 'checkbox', array(
                'required'   => false, 
                'label'      => 'Envoyer le mail de rappel d\'évalution ?'
            ))

            ->add('mailAlerteEvaluation', 'checkbox', array(
                'required'   => false, 
                'label'      => 'Envoyer le mail d\'alerte pour l\'évaluation ?'
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
