<?php

/**
 * Formulaire d'édition/ajout des utilisateurs
 * 
 * @author Quentin SOMAZZI
 * @copyright Nodevo
 */
namespace HopitalNumerique\UserBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    private $_constraints = array();
    private $_managerRole;

    public function __construct($manager, $validator, $managerRole)
    {
        $this->_constraints = $manager->getConstraints( $validator );
        $this->_managerRole = $managerRole;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class'      => 'HopitalNumerique\UserBundle\Entity\User',
                'csrf_protection' => false,
                'csrf_field_name' => '_token',
                // une clé unique pour aider à la génération du jeton secret
                'intention'       => 'task_item',
        ));
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
        $datas = $options['data'];
        $roles = $datas->getRoles();

        $builder
            ->add('username', 'text', array(
                'max_length' => $this->_constraints['username']['maxlength'],
                'required'   => true, 
                'label'      => 'Nom d\'utilisateur',
                'attr'       => array('class' => $this->_constraints['username']['class'] )
            ))

            ->add('nom', 'text', array(
                'max_length' => $this->_constraints['nom']['maxlength'],
                'required'   => true, 
                'label'      => 'Nom',
                'attr'       => array('class' => $this->_constraints['nom']['class'] )
            ))

            ->add('prenom', 'text', array(
                'max_length' => $this->_constraints['prenom']['maxlength'],
                'required'   => true, 
                'label'      => 'Prénom',
                'attr'       => array('class' => $this->_constraints['prenom']['class'] )
            ));   

            if( is_null($datas->getId()) ) {
                $builder
                ->add('plainPassword', 'text', array(
                        'required' => false,
                        'label'    => 'Mot de passe',
                        'disabled' => true,
                        'attr'     => array('placeholder' => 'Le mot de passe sera généré automatiquement')
                ));
            }else {
                $builder
                ->add('plainPassword', 'repeated', array(
                        'type'           => 'password',
                        'required'       => true,
                        'first_options'  => array('label' => 'Mot de passe', 'attr' => array('autocomplete' => 'off') ),
                        'second_options' => array('label' => 'Confirmer le mot de passe', 'attr' => array('autocomplete' => 'off') )
                ));
            }

            $builder->add('email', 'email', array(
                'max_length' => $this->_constraints['email']['maxlength'], 
                'required'   => true, 
                'label'      => 'Adresse email',
                'attr'       => array(
                                        'autocomplete' => 'off',
                                        'class'        => $this->_constraints['email']['class']
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

            ->add('titre', 'entity', array(
                    'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property'      => 'libelle',
                    'required'      => false,
                    'label'         => 'Titre',
                    'empty_value'   => ' - ',
                    'attr'          => array(),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                            ->where('ref.code = :etat')
                            ->setParameter('etat', 'TITRE')
                            ->orderBy('ref.order', 'ASC');
                    }
            ))
            
            ->add('telephoneDirect', 'text', array(
                'max_length' => $this->_constraints['telephoneDirect']['maxlength'],
                'required'   => false, 
                'label'      => 'Téléphone direct',
                'attr'       => array(
                                        'class'     => $this->_constraints['telephoneDirect']['class'], 
                                        'data-mask' => $this->_constraints['telephoneDirect']['mask'] 
                                    )
            ))

            ->add('telephonePortable', 'text', array(
                    'max_length' => $this->_constraints['telephonePortable']['maxlength'],
                    'required'   => false,
                    'label'      => 'Téléphone portable',
                    'attr'       => array(
                                        'class'     => $this->_constraints['telephonePortable']['class'], 
                                        'data-mask' => $this->_constraints['telephonePortable']['mask'] 
                                    )
            ))
            
            
            ->add('roles', 'genemu_jqueryselect2_entity', array(
                'class'         => 'NodevoRoleBundle:Role',
                'property'      => 'name',
                'multiple'      => false,
                'required'      => true,
                'label'         => 'Groupe associé',
                'mapped'        => false,
                'empty_value'   => 'Choissisez un groupe',
                //'attr'          => array('class'=>'validate[required]'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('ro')
                              ->where('ro.etat != :etat')
                              ->setParameter('etat', 4);
                },
                'data' => $this->_managerRole->findOneBy( array('role'=>$roles[0]) )
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

            ->add('etat', 'entity', array(
                'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                'property'    => 'libelle',
                'required'    => true,
                'label'       => 'Etat',
                'attr'        => array('class' => $this->_constraints['etat']['class'] ),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                              ->where('ref.code = :etat')
                              ->setParameter('etat', 'ETAT')
                              ->orderBy('ref.order', 'ASC');
                }
            ))
            
            ->add('contactAutre', 'textarea', array(
                    'required'   => false,
                    'label'      => 'Contact autre',
                    'attr'       => array()
            ))
            
            ;

            // ^ -------- Onglet : Vous êtes un établissement de santé -------- ^
            $builder->add('statutEtablissementSante', 'entity', array(
                    'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property'    => 'libelle',
                    'required'    => false,
                    'label'       => 'Statut de l\'établissement',
                    'empty_value' => ' - ',
                    'attr'        => array(),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                        ->where('ref.code = :etat')
                        ->setParameter('etat', 'STATUT_ETABLISSEMENT')
                        ->orderBy('ref.libelle', 'ASC');
                    }
            ))

            ->add('etablissementRattachementSante', 'entity', array(
                    'class'       => 'HopitalNumeriqueEtablissementBundle:Etablissement',
                    'property'    => 'usersAffichage',
                    'required'    => false,
                    'empty_value' => ' - ',
                    'label'       => 'Etablissement de rattachement',
                    'attr'        => array()
            ))

            ->add('autreStructureRattacheementSante', 'text', array(
                    'max_length' => $this->_constraints['autreStructureRattacheementSante']['maxlength'],
                    'required'   => false,
                    'label'      => 'Autre structure de rattachement',
                    'attr'       => array('class' => $this->_constraints['autreStructureRattacheementSante']['class'] )
            ))
            
            ->add('fonctionEtablissementSante', 'entity', array(
                    'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property'    => 'libelle',
                    'required'    => false,
                    'label'       => 'Fonction de l\'établissement',
                    'empty_value' => ' - ',
                    'attr'        => array(),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                        ->where('ref.code = :etat')
                        ->setParameter('etat', 'FONCTION_ES')
                        ->orderBy('ref.libelle', 'ASC');
                    }
            ))
            
            ->add('raisonInscriptionSante', 'entity', array(
                    'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property'    => 'libelle',
                    'required'    => false,
                    'label'       => 'Raison de l \'inscription',
                    'empty_value' => ' - ',
                    'attr'        => array(),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                        ->where('ref.code = :etat')
                        ->setParameter('etat', 'RAISON_INSCRIPTION')
                        ->orderBy('ref.libelle', 'ASC');
                    }
            ))
            
            ;

            // v -------- Onglet : Vous êtes un établissement de santé -------- v
            
            // ^ -------- Onglet : Vous êtes une structure autre qu'un établissement de santé  -------- ^
            
            $builder->add('nomStructure', 'text', array(
                    'max_length' => $this->_constraints['nomStructure']['maxlength'],
                    'required'   => false,
                    'label'      => 'Nom de la structure',
                    'attr'       => array('class' => $this->_constraints['nomStructure']['class'] )
            ))
            
            ->add('fonctionStructure', 'text', array(
                    'max_length' => $this->_constraints['fonctionStructure']['maxlength'],
                    'required'   => false,
                    'label'      => 'Fonction de la structure',
                    'attr'       => array('class' => $this->_constraints['fonctionStructure']['class'] )
            ))
            
            ->add('raisonInscriptionStructure', 'entity', array(
                    'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                    'property'    => 'libelle',
                    'required'    => false,
                    'label'       => 'Raison de l \'inscription',
                    'empty_value' => ' - ',
                    'attr'        => array(),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                        ->where('ref.code = :etat')
                        ->setParameter('etat', 'RAISON_INSCRIPTION')
                        ->orderBy('ref.libelle', 'ASC');
                    }
            ))
            
            ;
            
            // v -------- Onglet : Vous êtes une structure autre qu'un établissement de santé  -------- v
            
            $builder->add('save', 'button', array(
                    'attr'  => array('class' => 'save btn btn-success pull-right submit'),
                    'label' => 'INSCRIPTION'
            ));
    }

    /**
     * Retourne le nom du formulaire
     * @return string Nom du formulaire
     */
    public function getName()
    {
        return 'nodevo_user_user';
    }
}