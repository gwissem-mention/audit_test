<?php

/**
 * Formulaire d'édition/ajout des utilisateurs.
 */
namespace HopitalNumerique\UserBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\EtablissementBundle\Entity\Etablissement;
use HopitalNumerique\EtablissementBundle\Manager\EtablissementManager;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Form\Type\HobbyType;
use Nodevo\RoleBundle\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Nodevo\RoleBundle\Manager\RoleManager;
use Nodevo\ToolsBundle\Manager\Manager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class UserType
 */
class UserType extends AbstractType
{
    /**
     * @var array
     */
    private $constraints = [];

    /**
     * @var RoleManager
     */
    private $managerRole;

    /**
     * @var SecurityContext
     */
    private $securityContext;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var ReferenceManager
     */
    private $referenceManager;

    /**
     * @var EtablissementManager
     */
    private $etablissementManager;

    /**
     * UserType constructor.
     *
     * @param Manager              $manager
     * @param                      $validator
     * @param                      $managerRole
     * @param                      $securityContext
     * @param UserManager          $userManager
     * @param ReferenceManager     $referenceManager
     * @param EtablissementManager $etablissementManager
     */
    public function __construct(
        $manager,
        $validator,
        $managerRole,
        $securityContext,
        UserManager $userManager,
        ReferenceManager $referenceManager,
        EtablissementManager $etablissementManager
    ) {
        $this->constraints          = $manager->getConstraints($validator);
        $this->managerRole          = $managerRole;
        $this->securityContext      = $securityContext;
        $this->userManager          = $userManager;
        $this->referenceManager     = $referenceManager;
        $this->etablissementManager = $etablissementManager;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
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
        $connectedUser = $this->userManager->getUserConnected();

        $builder
            ->add('lastname', TextType::class, [
                'max_length' => $this->constraints['lastname']['maxlength'],
                'required' => true,
                'label' => 'Nom',
                'attr' => ['class' => $this->constraints['lastname']['class']],
            ])
            ->add('firstname', TextType::class, [
                'max_length' => $this->constraints['firstname']['maxlength'],
                'required' => true,
                'label' => 'Prénom',
                'attr' => ['class' => $this->constraints['firstname']['class']],
            ])
            ->add('email', EmailType::class, [
                'max_length' => $this->constraints['email']['maxlength'],
                'required' => true,
                'label' => 'Adresse email',
                'attr' => [
                    'autocomplete' => 'off',
                    'class' => $this->constraints['email']['class'],
                ],
            ])
            ->add('pseudonym', TextType::class, [
                'max_length' => $this->constraints['pseudonym']['maxlength'],
                'required' => false,
                'label' => 'Pseudonyme pour le forum',
                'attr' => ['class' => $this->constraints['pseudonym']['class']],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => 'password',
                'invalid_message' => 'Ces deux champs doivent être identiques.',
                'required' => true,
                'first_options' => ['label' => 'Mot de passe', 'attr' => ['autocomplete' => 'off']],
                'second_options' => ['label' => 'Confirmer le mot de passe', 'attr' => ['autocomplete' => 'off']],
            ])
            ->add('phoneNumber', TextType::class, [
                'max_length' => $this->constraints['phoneNumber']['maxlength'],
                'required' => false,
                'label' => 'Téléphone fixe',
                'attr' => [
                    'class' => $this->constraints['phoneNumber']['class'],
                    'data-mask' => $this->constraints['phoneNumber']['mask'],
                ],
            ])
            ->add('cellPhoneNumber', TextType::class, [
                'max_length' => $this->constraints['cellPhoneNumber']['maxlength'],
                'required' => false,
                'label' => 'Téléphone portable',
                'attr' => [
                    'class' => $this->constraints['cellPhoneNumber']['class'],
                    'data-mask' => $this->constraints['cellPhoneNumber']['mask'],
                ],
            ])
            ->add('otherContact', TextareaType::class, [
                'required' => false,
                'label' => 'Autres contacts',
                'attr' => [],
            ])
            ->add('profileType', EntityType::class, [
                'class' => Reference::class,
                'choices' => $this->referenceManager->findByCode('CONTEXTE_METIER_INTERNAUTE'),
                'choice_label' => 'libelle',
                'required' => false,
                'label' => 'Profil',
                'empty_value' => ' - ',
                'attr' => ['class' => 'etablissement_sante'],
            ])
            ->add('jobType', EntityType::class, [
                'class' => Reference::class,
                'choices' => $this->referenceManager->findByCode('CONTEXTE_FONCTION_INTERNAUTE'),
                'choice_label' => 'libelle',
                'required' => false,
                'label' => 'Rôle',
                'empty_value' => ' - ',
                'attr' => ['class' => 'etablissement_sante'],
            ])
            ->add('jobLabel', TextType::class, [
                'max_length' => $this->constraints['jobLabel']['maxlength'],
                'required' => false,
                'label' => 'Libellé rôle',
                'attr' => [
                    'class' => $this->constraints['jobLabel']['class'] . ' etablissement_sante',
                ],
            ])
            ->add('activities', EntityType::class, [
                'class' => Reference::class,
                'choices' => $this->referenceManager->findByCode('CONTEXTE_SPECIALITE_ES'),
                'choice_label' => 'libelle',
                'required' => false,
                'expanded' => false,
                'multiple' => true,
                'label' => 'Type d\'activité',
                'empty_value' => ' - ',
                'attr' => ['class' => 'etablissement_sante'],
            ])
            ->add('organizationType', EntityType::class, [
                'class' => Reference::class,
                'choices' => $this->referenceManager->findByCode('CONTEXTE_TYPE_ES'),
                'choice_label' => 'libelle',
                'required' => false,
                'label' => 'Type de structure',
                'empty_value' => ' - ',
                'attr' => ['class' => 'etablissement_sante'],
            ])
            ->add('organizationLabel', TextType::class, [
                'max_length' => $this->constraints['organizationLabel']['maxlength'],
                'required' => false,
                'label' => 'Nom de votre structure si non disponible dans la liste précédente',
                'attr' => [
                    'class' => $this->constraints['organizationLabel']['class'] . ' etablissement_sante',
                ],
            ])
            ->add('computerSkills', EntityType::class, [
                'class' => Reference::class,
                'choices' => $this->referenceManager->findByCode('LOGICIELS'),
                'choice_label' => 'libelle',
                'multiple' => true,
                'required' => false,
                'label' => 'Logiciels maîtrisés'
            ])
            ->add('presentation', TextareaType::class, [
                'label' => 'Présentation',
                'required' => false,
                'attr' => [
                    'rows' => 8,
                ],
            ])
            ->add('hobbies', CollectionType::class, [
                'type' => HobbyType::class,
                'label' => 'Centre d\'intérêt',
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
            ->add('region', EntityType::class, [
                'class' => Reference::class,
                'choices' => $this->referenceManager->findByCode('REGION'),
                'choice_label' => 'libelle',
                'required' => false,
                'label' => 'Région',
                'empty_value' => ' - ',
            ])
            ->add('county', EntityType::class, [
                'class' => Reference::class,
                'choices' => $this->referenceManager->findByCode('DEPARTEMENT'),
                'choice_label' => 'libelle',
                'required' => false,
                'label' => 'Département',
                'empty_value' => ' - ',
                'attr' => [],
            ])
            ->add('inscritCommunautePratique', CheckboxType::class, [
                'label' => 'Membre de la communauté de pratique',
            ])
            ->add('etat', EntityType::class, [
                'class' => Reference::class,
                'choices' => $this->referenceManager->findByCode('ETAT'),
                'choice_label' => 'libelle',
                'required' => true,
                'label' => 'Etat',
                'attr' => ['class' => $this->constraints['etat']['class']],
            ])
            ->add('roles', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'name',
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

                    if (!$this->securityContext->isGranted('ROLE_ADMINISTRATEUR_1')) {
                        $qb->andWhere('ro.id NOT IN (:rolesAdmins)')
                           ->setParameter('rolesAdmins', [1, 106])
                        ;
                    }

                    $qb->orderBy('ro.name');

                    return $qb;
                },
                'data' => $this->managerRole->findOneBy(['role' => $roles[0]]),
            ])
            ->add('domaines', EntityType::class, [
                'class' => Domaine::class,
                'choice_label' => 'nom',
                'required' => true,
                'multiple' => true,
                'label' => 'Domaine(s) concerné(s)',
                'empty_value' => ' - ',
                'attr' => ['class' => 'validate[required]'],
                'query_builder' => function (EntityRepository $er) use ($connectedUser) {
                    if ($this->securityContext->isGranted('ROLE_ADMINISTRATEUR_1')) {
                        return $er->createQueryBuilder('dom')->orderBy('dom.nom');
                    } else {
                        return $er->getDomainesUserConnectedForForm($connectedUser->getId());
                    }
                },
            ])
            ->add('remarque', TextareaType::class, [
                'required' => false,
                'label' => 'Remarque pour la gestion',
            ])
            ->add('biographie', TextareaType::class, [
                'required' => false,
                'label' => 'Biographie',
                'attr' => [
                    'rows' => 8,
                ],
            ])
            ->add('raisonDesinscription', TextareaType::class, [
                'required' => false,
                'label' => 'Raison de la désinscription',
            ])
            ->add('file', FileType::class, [
                'required' => false,
                'label' => 'Photo de profil',
            ])
            ->add('path', HiddenType::class)
        ;

        if ($builder->getData()->hasRoleAmbassadeur()) {
            $builder
                ->add('rattachementRegions', EntityType::class, [
                    'class' => Reference::class,
                    'label' => 'Régions de rattachement',
                    'multiple' => true,
                    'expanded' => false,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('ref')
                            ->leftJoin('ref.codes', 'codes')
                            ->where('codes.label = :etat')
                            ->setParameter('etat', 'REGION')
                            ->orderBy('ref.order', 'ASC')
                        ;
                    },
                    'choice_label' => 'libelle',
                    'attr' => [
                        'size' => 8,
                    ],
                ]);
        }

        $organizationFieldOptions = [
            'class'        => Etablissement::class,
            'choice_label' => 'appellation',
            'required'     => false,
            'label'        => 'Structure de rattachement',
            'multiple'     => false,
            'empty_value'  => '-',
            'data'         => is_null($currentResponse) ? null : $currentResponse->getOrganization(),
        ];

        $organizationModifier = function (
            FormInterface $form,
            $etabId = null
        ) use (
            $currentResponse,
            $organizationFieldOptions
        ) {

            if (!is_null($etabId)) {
                $organizationFieldOptions = array_merge(
                    $organizationFieldOptions,
                    [
                        'query_builder' => function (EntityRepository $er) use ($etabId) {
                            return $er->createQueryBuilder('eta')
                                  ->orderBy('eta.nom', 'ASC')
                            ;
                        },
                    ]
                );
            } else {
                $organizationFieldOptions['choices'] =
                    is_null($currentResponse) || is_null($currentResponse->getOrganization())
                        ? []
                        : [$currentResponse->getOrganization()]
                ;
            }

            $form->add('organization', EntityType::class, $organizationFieldOptions);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($organizationModifier) {
                $organizationModifier($event->getForm());
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($organizationModifier) {
                $organizationModifier($event->getForm(), $event->getForm()->getData());
            }
        );
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
