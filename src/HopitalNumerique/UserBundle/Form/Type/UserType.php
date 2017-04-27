<?php

/**
 * Formulaire d'édition/ajout des utilisateurs.
 */

namespace HopitalNumerique\UserBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\EtablissementBundle\Manager\EtablissementManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

class UserType extends AbstractType
{
    private $_constraints = [];
    private $_managerRole;
    private $_securityContext;
    private $_userManager;
    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager
     */
    private $referenceManager;

    /** @var EtablissementManager */
    private $etablissementManager;

    public function __construct(
        $manager,
        $validator,
        $managerRole,
        $securityContext,
        UserManager $userManager,
        ReferenceManager $referenceManager,
        EtablissementManager $etablissementManager
    ) {
        $this->_constraints = $manager->getConstraints($validator);
        $this->_managerRole = $managerRole;
        $this->_securityContext = $securityContext;
        $this->_userManager = $userManager;
        $this->referenceManager = $referenceManager;
        $this->etablissementManager = $etablissementManager;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\UserBundle\Entity\User',
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            // une clé unique pour aider à la génération du jeton secret
            'intention' => 'task_item',
        ]);
    }

    /**
     * Ajout des éléments dans le formulaire, spécifie les labels, les widgets utilisés ainsi que l'obligation.
     *
     * @param FormBuilderInterface $builder Le builder contient les champs du formulaire
     * @param array                $options Data passée au formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $currentResponse = $builder->getData();
        $datas = $options['data'];
        $roles = $datas->getRoles();
        $connectedUser = $this->_userManager->getUserConnected();

        $builder->add('pseudonymeForum', 'text', [
            'max_length' => $this->_constraints['pseudonymeForum']['maxlength'],
            'required' => false,
            'label' => 'Pseudonyme pour le forum',
            'attr' => ['class' => $this->_constraints['pseudonymeForum']['class']],
        ]);

        $builder
            ->add('nom', 'text', [
                'max_length' => $this->_constraints['nom']['maxlength'],
                'required' => true,
                'label' => 'Nom',
                'attr' => ['class' => $this->_constraints['nom']['class']],
            ])
            ->add('prenom', 'text', [
                'max_length' => $this->_constraints['prenom']['maxlength'],
                'required' => true,
                'label' => 'Prénom',
                'attr' => ['class' => $this->_constraints['prenom']['class']],
            ])
        ;

        if (is_null($datas->getId())) {
            $builder
                ->add('plainPassword', 'text', [
                    'required' => false,
                    'label' => 'Mot de passe',
                    'disabled' => true,
                    'attr' => ['placeholder' => 'Le mot de passe sera généré automatiquement'],
                ]);
        } else {
            $builder
                ->add('plainPassword', 'repeated', [
                    'type' => 'password',
                    'invalid_message' => 'Ces deux champs doivent être identiques.',
                    'required' => true,
                    'first_options' => ['label' => 'Mot de passe', 'attr' => ['autocomplete' => 'off']],
                    'second_options' => ['label' => 'Confirmer le mot de passe', 'attr' => ['autocomplete' => 'off']],
                ]);
        }

        $builder->add('email', 'email', [
            'max_length' => $this->_constraints['email']['maxlength'],
            'required' => true,
            'label' => 'Adresse email',
            'attr' => [
                'autocomplete' => 'off',
                'class' => $this->_constraints['email']['class'],
            ],
        ])
            ->add('civilite', 'entity', [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('CIVILITE'),
                'property' => 'libelle',
                'required' => true,
                'label' => 'Civilite',
                'empty_value' => ' - ',
                'attr' => ['class' => $this->_constraints['civilite']['class']],
            ])
            ->add('titre', 'entity', [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('TITRE'),
                'property' => 'libelle',
                'required' => false,
                'label' => 'Titre',
                'empty_value' => ' - ',
                'attr' => [],
            ])
            ->add('telephoneDirect', 'text', [
                'max_length' => $this->_constraints['telephoneDirect']['maxlength'],
                'required' => false,
                'label' => 'Téléphone direct',
                'attr' => [
                    'class' => $this->_constraints['telephoneDirect']['class'],
                    'data-mask' => $this->_constraints['telephoneDirect']['mask'],
                ],
            ])
            ->add('telephonePortable', 'text', [
                'max_length' => $this->_constraints['telephonePortable']['maxlength'],
                'required' => false,
                'label' => 'Téléphone portable',
                'attr' => [
                    'class' => $this->_constraints['telephonePortable']['class'],
                    'data-mask' => $this->_constraints['telephonePortable']['mask'],
                ],
            ])
        ;

        //Si il y a un utilisateur connecté nous sommes en BO ou dans informations perso
        if ($this->_securityContext->isGranted('ROLE_USER')) {
            $builder->add('roles', 'entity', [
                'class' => 'NodevoRoleBundle:Role',
                'property' => 'name',
                'required' => true,
                'label' => 'Groupe associé',
                'mapped' => false,
                'empty_value' => ' - ',
                'attr' => ['class' => 'validate[required]'],
                'query_builder' => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('ro')
                        ->where('ro.etat != :etat')
                        ->setParameter('etat', 4)
                    ;

                    if (!$this->_securityContext->isGranted('ROLE_ADMINISTRATEUR_1')) {
                        $qb->andWhere('ro.id NOT IN (:rolesAdmins)')
                            ->setParameter('rolesAdmins', [1, 106])
                        ;
                    }

                    $qb->orderBy('ro.name');

                    return $qb;
                },
                'data' => $this->_managerRole->findOneBy(['role' => $roles[0]]),
            ])
                ->add('domaines', 'entity', [
                    'class' => 'HopitalNumeriqueDomaineBundle:Domaine',
                    'property' => 'nom',
                    'required' => true,
                    'multiple' => true,
                    'label' => 'Domaine(s) concerné(s)',
                    'empty_value' => ' - ',
                    'attr' => ['class' => 'validate[required]'],
                    'query_builder' => function (EntityRepository $er) use ($connectedUser) {
                        if ($this->_securityContext->isGranted('ROLE_ADMINISTRATEUR_1')) {
                            return $er->createQueryBuilder('dom')->orderBy('dom.nom');
                        } else {
                            return $er->getDomainesUserConnectedForForm($connectedUser->getId());
                        }
                    },
                ])
                ->add('remarque', 'textarea', [
                    'required' => false,
                    'label' => 'Remarque pour la gestion',
                ])
                ->add('biographie', 'textarea', [
                    'required' => false,
                    'label' => 'Biographie',
                    'attr' => [
                        'rows' => 8,
                    ],
                ])
                ->add('raisonDesinscription', 'textarea', [
                    'required' => false,
                    'label' => 'Raison de la désinscription',
                ])
                ->add('file', 'file', [
                    'required' => false,
                    'label' => 'Photo de profil',
                ])
                ->add('path', 'hidden')
            ;
        }

        //Si il y a un utilisateur connecté nous sommes en BO et que le role est CMSI
        if (!($this->_securityContext->isGranted('ROLE_ARS_CMSI_4'))) {
            $builder->add('region', 'entity', [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('REGION'),
                'property' => 'libelle',
                'required' => false,
                'label' => 'Région',
                'empty_value' => ' - ',
            ])
                ->add('departement', 'entity', [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices' => $this->referenceManager->findByCode('DEPARTEMENT'),
                    'property' => 'libelle',
                    'required' => false,
                    'label' => 'Département',
                    'empty_value' => ' - ',
                    'attr' => [],
                ])
            ;
        }

        $builder->add('etat', 'entity', [
            'class' => 'HopitalNumeriqueReferenceBundle:Reference',
            'choices' => $this->referenceManager->findByCode('ETAT'),
            'property' => 'libelle',
            'required' => true,
            'label' => 'Etat',
            'attr' => ['class' => $this->_constraints['etat']['class']],
        ]);

        $builder->add('inscritCommunautePratique', 'checkbox', [
            'label' => 'Membre de la communauté de pratique',
        ]);

        $builder->add('contactAutre', 'textarea', [
            'required' => false,
            'label' => 'Contact autre',
            'attr' => [],
        ]);

        if ($builder->getData()->hasRoleAmbassadeur()) {
            $builder
                ->add('rattachementRegions', 'entity', [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'label' => 'Régions de rattachement',
                    'multiple' => true,
                    'expanded' => false,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                            ->where('ref.code = :etat')
                            ->setParameter('etat', 'REGION')
                            ->orderBy('ref.order', 'ASC')
                            ;
                    },
                    'property' => 'libelle',
                    'attr' => [
                        'size' => 8,
                    ],
                ]);
        }

        $builder->add('statutEtablissementSante', 'entity', [
            'class' => 'HopitalNumeriqueReferenceBundle:Reference',
            'choices' => $this->referenceManager->findByCode('CONTEXTE_TYPE_ES'),
            'property' => 'libelle',
            'required' => false,
            'label' => 'Type de structure',
            'empty_value' => ' - ',
            'attr' => ['class' => 'etablissement_sante'],
        ]);

        $etablissementFieldOptions = [
            'class'        => 'HopitalNumeriqueEtablissementBundle:Etablissement',
            'choice_label' => 'appellation',
            'required'     => false,
            'label'        => 'Structure de rattachement',
            'multiple'     => false,
            'empty_value'  => '-',
            'attr'         => ['class' => 'ajax-select2-list', 'data-url' => '/etablissement/load/'],
            'data'         => is_null($currentResponse) ? null : $currentResponse->getEtablissementRattachementSante(),
        ];

        $etablissementRattachementSanteModifier = function (
            FormInterface $form,
            $etabId = null
        ) use (
            $currentResponse,
            $etablissementFieldOptions
        ) {

            if (!is_null($etabId)) {
                $etablissementFieldOptions = array_merge(
                    $etablissementFieldOptions,
                    [
                        'query_builder' => function (EntityRepository $er) use ($etabId) {
                            return $er->createQueryBuilder('eta')
                                  ->orderBy('eta.nom', 'ASC')
                            ;
                        },
                    ]
                );
            } else {
                $etablissementFieldOptions['choices'] =
                    is_null($currentResponse) || is_null($currentResponse->getEtablissementRattachementSante())
                        ? []
                        : [$currentResponse->getEtablissementRattachementSante()]
                ;
            }

            $form->add('etablissementRattachementSante', EntityType::class, $etablissementFieldOptions);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($etablissementRattachementSanteModifier) {
                $etablissementRattachementSanteModifier($event->getForm());
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($etablissementRattachementSanteModifier) {
                $etablissementRattachementSanteModifier($event->getForm(), $event->getForm()->getData());
            }
        );

        $builder->add('autreStructureRattachementSante', 'text', [
            'max_length' => $this->_constraints['autreStructureRattachementSante']['maxlength'],
            'required' => false,
            'label' => 'Nom de votre structure si non disponible dans la liste précédente',
            'attr' => ['class' => $this->_constraints['autreStructureRattachementSante']['class'] . ' etablissement_sante'],
        ])
            ->add('fonctionDansEtablissementSante', 'text', [
                'max_length' => $this->_constraints['fonctionDansEtablissementSante']['maxlength'],
                'required' => false,
                'label' => 'Libellé fonction',
                'attr' => ['class' => $this->_constraints['fonctionDansEtablissementSante']['class'] . ' etablissement_sante'],
            ])
            ->add('fonctionDansEtablissementSanteReferencement', 'entity', [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('CONTEXTE_FONCTION_INTERNAUTE'),
                'property' => 'libelle',
                'required' => false,
                'label' => 'Fonction',
                'empty_value' => ' - ',
                'attr' => ['class' => 'etablissement_sante'],
            ])
            ->add('typeActivite', 'entity', [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('CONTEXTE_SPECIALITE_ES'),
                'property' => 'libelle',
                'required' => false,
                'expanded' => false,
                'multiple' => true,
                'label' => 'Type activité (pour les établissements sanitaires)',
                'empty_value' => ' - ',
                'attr' => ['class' => 'etablissement_sante'],
            ])
            ->add('profilEtablissementSante', 'entity', [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('CONTEXTE_METIER_INTERNAUTE'),
                'property' => 'libelle',
                'required' => false,
                'label' => 'Profil',
                'empty_value' => ' - ',
                'attr' => ['class' => 'etablissement_sante'],
            ])
        ;

        $builder->add('nomStructure', 'text', [
            'max_length' => $this->_constraints['nomStructure']['maxlength'],
            'required' => false,
            'label' => 'Nom de la structure',
            'attr' => ['class' => $this->_constraints['nomStructure']['class'] . ' autre_structure'],
        ])
            ->add('fonctionStructure', 'text', [
                'max_length' => $this->_constraints['fonctionStructure']['maxlength'],
                'required' => false,
                'label' => 'Fonction dans la structure',
                'attr' => ['class' => $this->_constraints['fonctionStructure']['class'] . ' autre_structure'],
            ])
        ;

        // v -------- Onglet : Vous êtes dans une autre structure  -------- v

        // Conditions générales d'utilisation - Uniquement en FO = Si l'utilisateur n'est pas connecté
        if (!$this->_securityContext->isGranted('ROLE_USER')) {
            $builder->add('termsAccepted', 'checkbox', [
                'required' => true,
                'label' => 'J\'accepte les conditions générales d\'utilisation de la plateforme',
                'label_attr' => ['class' => 'conditonsGenerales'],
                'attr' => ['class' => $this->_constraints['termsAccepted']['class'] . ' checkbox'],
            ]);
        }
    }

    /**
     * Retourne le nom du formulaire.
     *
     * @return string Nom du formulaire
     */
    public function getName()
    {
        return 'nodevo_user_user';
    }
}
