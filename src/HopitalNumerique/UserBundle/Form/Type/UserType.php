<?php

/**
 * Formulaire d'édition/ajout des utilisateurs
 */
namespace HopitalNumerique\UserBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

class UserType extends AbstractType
{
    private $_constraints = array();
    private $_managerRole;
    private $_securityContext;
    private $_userManager;
    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager
     */
    private $referenceManager;

    public function __construct($manager, $validator, $managerRole, $securityContext, UserManager $userManager, ReferenceManager $referenceManager)
    {
        $this->_constraints = $manager->getConstraints( $validator );
        $this->_managerRole = $managerRole;
        $this->_securityContext = $securityContext;
        $this->_userManager = $userManager;
        $this->referenceManager = $referenceManager;
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
        $connectedUser = $this->_userManager->getUserConnected();

        $builder->add('username', 'text', array(
                'max_length' => $this->_constraints['username']['maxlength'],
                'required'   => true, 
                'label'      => 'Identifiant (login)',
                'attr'       => array('class' => $this->_constraints['username']['class'] )
            ));

        $builder->add('pseudonymeForum', 'text', array(
                'max_length' => $this->_constraints['pseudonymeForum']['maxlength'],
                'required'   => false, 
                'label'      => 'Pseudonyme pour le forum',
                'attr'       => array('class' => $this->_constraints['pseudonymeForum']['class'] )
            ));

        $builder
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
                        'invalid_message' => 'Ces deux champs doivent être identiques.',
                        'required'       => true,
                        'first_options'  => array('label' => 'Mot de passe', 'attr' => array('autocomplete' => 'off') ),
                        'second_options' => array('label' => 'Confirmer le mot de passe', 'attr' => array('autocomplete' => 'off'))
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
                    'choices'       => $this->referenceManager->findByCode('CIVILITE'),
                    'property'      => 'libelle',
                    'required'      => true,
                    'label'         => 'Civilite',
                    'empty_value'   => ' - ',
                    'attr'          => array('class' => $this->_constraints['civilite']['class'] ),
            ))

            ->add('titre', 'entity', array(
                    'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices'       => $this->referenceManager->findByCode('TITRE'),
                    'property'      => 'libelle',
                    'required'      => false,
                    'label'         => 'Titre',
                    'empty_value'   => ' - ',
                    'attr'          => array(),
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
            ));
            

            //Si il y a un utilisateur connecté nous sommes en BO ou dans informations perso
            if($this->_securityContext->isGranted('ROLE_USER'))
            {
                $builder->add('roles', 'entity', array(
                        'class'         => 'NodevoRoleBundle:Role',
                        'property'      => 'name',
                        'required'      => true,
                        'label'         => 'Groupe associé',
                        'mapped'        => false,
                        'empty_value'   => ' - ',
                        'attr'          => array('class'=>'validate[required]'),
                        'query_builder' => function(EntityRepository $er) {
                            $qb = $er->createQueryBuilder('ro')
                                      ->where('ro.etat != :etat')
                                      ->setParameter('etat', 4);

                            if(!$this->_securityContext->isGranted('ROLE_ADMINISTRATEUR_1'))
                            {
                                $qb->andWhere('ro.id NOT IN (:rolesAdmins)')
                                    ->setParameter('rolesAdmins' , array(1, 106));
                            }

                            $qb->orderBy('ro.name');

                            return $qb;
                        },
                        'data' => $this->_managerRole->findOneBy( array('role'=>$roles[0]) )
                    ))
                    ->add('domaines', 'entity', array(
                            'class'       => 'HopitalNumeriqueDomaineBundle:Domaine',
                            'property'    => 'nom',
                            'required'    => false,
                            'multiple'    => true,
                            'label'       => 'Domaine(s) concerné(s)',
                            'empty_value' => ' - ',
                            'query_builder' => function(EntityRepository $er) use ($connectedUser) { 
                                if($this->_securityContext->isGranted('ROLE_ADMINISTRATEUR_1'))
                                {
                                    return $er->createQueryBuilder('dom')->orderBy('dom.nom');
                                }
                                else
                                {
                                    return $er->getDomainesUserConnectedForForm($connectedUser->getId());
                                }
                            }
                    ))
                    ->add('remarque', 'textarea', array(
                        'required'   => false, 
                        'label'      => 'Remarque pour la gestion'
                    ))
                    ->add('biographie', 'textarea', array(
                        'required'   => false, 
                        'label'      => 'Biographie',
                        'attr'       => array(
                            'rows' => 8
                        )
                    ))
                    ->add('raisonDesinscription', 'textarea', array(
                        'required'   => false, 
                        'label'      => 'Raison de la désinscription'
                    ))
                    ->add('file', 'file', array(
                        'required' => false, 
                        'label'    => 'Photo de profil'
                    ))
                    ->add('path', 'hidden');
            }

            //Si il y a un utilisateur connecté nous sommes en BO et que le role est CMSI
            if( !($this->_securityContext->isGranted('ROLE_ARS_CMSI_4')) )
            {
                $builder->add('region', 'entity', array(
                        'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                        'choices'       => $this->referenceManager->findByCode('REGION'),
                        'property'    => 'libelle',
                        'required'    => false,
                        'label'       => 'Région',
                        'empty_value' => ' - ',
                ))
                
                ->add('departement', 'entity', array(
                        'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                        'choices'       => $this->referenceManager->findByCode('DEPARTEMENT'),
                        'property'    => 'libelle',
                        'required'    => false,
                        'label'       => 'Département',
                        'empty_value' => ' - ',
                        'attr'        => array(),
                ));
            }
            
            $builder->add('etat', 'entity', array(
                'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices'       => $this->referenceManager->findByCode('ETAT'),
                'property'    => 'libelle',
                'required'    => true,
                'label'       => 'Etat',
                'attr'        => array('class' => $this->_constraints['etat']['class'] ),
            ));

            $builder->add('inscritCommunautePratique', 'checkbox', array(
                'label' => 'Membre de la communauté de pratiques'
            ));
            
            $builder->add('contactAutre', 'textarea', array(
                    'required'   => false,
                    'label'      => 'Contact autre',
                    'attr'       => array()
            ));

            // ^ -------- Onglet : Vous êtes un établissement de santé -------- ^
            $builder->add('statutEtablissementSante', 'entity', array(
                    'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices'       => $this->referenceManager->findByCode('CONTEXTE_TYPE_ES'),
                    'property'    => 'libelle',
                    'required'    => false,
                    'label'       => 'Type d\'établissement',
                    'empty_value' => ' - ',
                    'attr'        => array('class' => 'etablissement_sante'),
            ))
            
            ->add('etablissementRattachementSante', 'genemu_jqueryselect2_entity', array(
                    'class'         => 'HopitalNumeriqueEtablissementBundle:Etablissement',
                    'property'      => 'usersAffichage',
                    'multiple'      => false,
                    'required'      => false,
                    'label'         => 'Etablissement de rattachement',
                    'empty_value'   => ' - ',
                    'attr'        => array('class' => 'etablissement_sante')
            ))
            

            ->add('autreStructureRattachementSante', 'text', array(
                    'max_length' => $this->_constraints['autreStructureRattachementSante']['maxlength'],
                    'required'   => false,
                    'label'      => 'Nom de votre établissement si non disponible dans la liste précédente',
                    'attr'       => array('class' => $this->_constraints['autreStructureRattachementSante']['class'] . ' etablissement_sante' )
            ))
            

            ->add('fonctionDansEtablissementSante', 'text', array(
                    'max_length' => $this->_constraints['fonctionDansEtablissementSante']['maxlength'],
                    'required'   => false,
                    'label'      => 'Libellé fonction',
                    'attr'       => array('class' => $this->_constraints['fonctionDansEtablissementSante']['class'] . ' etablissement_sante' )
            ))
            
            ->add('fonctionDansEtablissementSanteReferencement', 'entity', array(
                    'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices'       => $this->referenceManager->findByCode('CONTEXTE_FONCTION_INTERNAUTE'),
                    'property'    => 'libelle',
                    'required'    => false,
                    'label'       => 'Fonction',
                    'empty_value' => ' - ',
                    'attr'        => array('class' => 'etablissement_sante'),
            ))
            
            ->add('typeActivite', 'entity', array(
                    'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices'       => $this->referenceManager->findByCode('CONTEXTE_SPECIALITE_ES'),
                    'property'    => 'libelle',
                    'required'    => false,
                    'multiple'    => true,
                    'label'       => 'Type activité',
                    'empty_value' => ' - ',
                    'attr'        => array('class' => 'etablissement_sante'),
            ))
            
            ->add('profilEtablissementSante', 'entity', array(
                    'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices'       => $this->referenceManager->findByCode('CONTEXTE_METIER_INTERNAUTE'),
                    'property'    => 'libelle',
                    'required'    => false,
                    'label'       => 'Profil',
                    'empty_value' => ' - ',
                    'attr'        => array('class' => 'etablissement_sante'),
            ))
            ;

            // v -------- Onglet : Vous êtes un établissement de santé -------- v
            
            // ^ -------- Onglet : Vous êtes une structure autre qu'un établissement de santé  -------- ^
            
            $builder->add('nomStructure', 'text', array(
                    'max_length' => $this->_constraints['nomStructure']['maxlength'],
                    'required'   => false,
                    'label'      => 'Nom de la structure',
                    'attr'       => array('class' => $this->_constraints['nomStructure']['class'] . ' autre_structure' )
            ))
            
            ->add('fonctionStructure', 'text', array(
                    'max_length' => $this->_constraints['fonctionStructure']['maxlength'],
                    'required'   => false,
                    'label'      => 'Fonction dans la structure',
                    'attr'       => array('class' => $this->_constraints['fonctionStructure']['class'] . ' autre_structure' )
            ))
            ;
            
            // v -------- Onglet : Vous êtes une structure autre qu'un établissement de santé  -------- v
            
            // Conditions générales d'utilisation - Uniquement en FO = Si l'utilisateur n'est pas connecté
            if(!$this->_securityContext->isGranted('ROLE_USER'))
                $builder->add('termsAccepted', 'checkbox', array(
                        'required'   => true,
                        'label'      => 'J\'accepte les conditions générales d\'utilisation de la plateforme',
                        'label_attr' => array('class' => 'conditonsGenerales'),
                        'attr'       => array('class' => $this->_constraints['termsAccepted']['class'] . ' checkbox')
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
