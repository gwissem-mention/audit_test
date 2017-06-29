<?php

namespace HopitalNumerique\UserBundle\Form\Type\User;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\EtablissementBundle\Manager\EtablissementManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class InformationsManquantesType
 */
class InformationsManquantesType extends AbstractType
{
    /**
     * @var int Type Communauté de pratique
     */
    const TYPE_COMMUNAUTE_PRATIQUE = 1;

    /**
     * @var int Type Demande d'intervention d'un ambssadeur
     */
    const TYPE_DEMANDE_INTERVENTION = 2;

    /**
     * @var RouterInterface Router
     */
    private $router;

    /**
     * @var ReferenceManager ReferenceManager
     */
    private $referenceManager;

    /**
     * @var User Utilisateur connecté
     */
    private $user;

    /**
     * @var Request Request
     */
    private $request;

    private $etablissementManager;

    /**
     * Constructeur.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param RouterInterface       $router
     * @param RequestStack          $requestStack
     * @param ReferenceManager      $referenceManager
     * @param EtablissementManager  $etablissementManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        RequestStack $requestStack,
        ReferenceManager $referenceManager,
        EtablissementManager $etablissementManager
    ) {
        $this->router = $router;
        $this->referenceManager = $referenceManager;

        $this->user = (null !== $tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null);
        $this->request = $requestStack->getCurrentRequest();
        $this->etablissementManager = $etablissementManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $informationsType = $options['informations_type'];

        $builder->setAction(
            $this->router->generate(
                'hopital_numerique_account_informationsmanquantes_save',
                ['informationsType' => $informationsType]
            )
        );

        $builder
            ->add('informationsType', HiddenType::class, [
                'mapped' => false,
                'data' => $informationsType,
            ])
            ->add('redirection', HiddenType::class, [
                'mapped' => false,
                'data' => $this->request->server->get('REDIRECT_URL'),
            ])
        ;
        foreach ($this->buildFields($builder, $informationsType) as $field => $configuration) {
            $builder
                ->add($field, $configuration['type'], $configuration['options'])
            ;
        }
    }

    /**
     * Retourne les champs du formulaire.
     *
     * @param FormBuilderInterface $builder
     * @param string               $informationsType Type des informations
     *
     * @return array Champs
     * @throws \Exception
     */
    private function buildFields(FormBuilderInterface $builder, $informationsType)
    {
        switch ($informationsType) {
            case self::TYPE_COMMUNAUTE_PRATIQUE:
                return $this->getCommunautePratiqueFields($builder);
            case self::TYPE_DEMANDE_INTERVENTION:
                return $this->getDemandeInterventionFields($builder);
            default:
                throw new \Exception('Type non reconnu pour le formulaire des informations manquantes.');
        }
    }

    /**
     * Retourne les champs du formulaire pour l'inscription à la communauté de pratique.
     *
     * @param FormBuilderInterface $builder
     *
     * @return array Champs
     */
    private function getCommunautePratiqueFields(FormBuilderInterface $builder)
    {
        $fields = [];

        $displayUserLastnameFirstname =
            empty(trim($this->user->getLastname()))
            || empty(trim($this->user->getFirstname()))
        ;

        $displayEtablissementNom =
            null === $this->user->getOrganization()
            && null === $this->user->getOrganizationLabel()
        ;

        $displayRegionDepartement =
            (null === $this->user->getRegion() || null === $this->user->getCounty())
            || $displayEtablissementNom
        ;

        if ($displayUserLastnameFirstname) {
            $this->buildLastnameField($builder);
            $this->buildFirstnameField($builder);
        }

        if ($displayRegionDepartement) {
            $this->buildRegionField($builder);
            $this->buildDepartementField($builder);

            if ($displayEtablissementNom) {
                $this->buildOrganizationTypeField($builder);
                $this->buildOrganizationField($builder);
                $this->buildOrganizationLabelField($builder);
            }
        }

        if (null === $this->user->getProfileType()) {
            $this->buildProfileTypeField($builder);
        }

        if (null === $this->user->getJobType()
            && null === $this->user->getJobLabel()
        ) {
            $builder->add('jobType', EntityType::class, [
                'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices'     => $this->referenceManager->findByCode('CONTEXTE_FONCTION_INTERNAUTE'),
                'property'    => 'libelle',
                'label'       => 'user.jobType',
                'empty_value' => ' - ',
                'required'    => false,
            ]);

            $builder->add('jobLabel', TextType::class, [
                'label'    => 'user.jobLabel',
                'required' => false,
                'attr'     => [
                    'maxlength' => 255,
                ],
            ]);
        }

        return $fields;
    }

    /**
     * Retourne les champs du formulaire pour une demande d'intervention.
     *
     * @param FormBuilderInterface $builder
     *
     * @return array Champs
     */
    private function getDemandeInterventionFields(FormBuilderInterface $builder)
    {
        $fields = [];

        $hasEtablissement = (null !== $this->user->getOrganization()
            || null !== $this->user->getOrganizationLabel()
        );

        if (null === $this->user->getPhoneNumber()) {
            $this->buildPhoneNumberField($builder);
        }
        if (null === $this->user->getRegion() || !$hasEtablissement) {
            $this->buildRegionField($builder);
        }
        if (null === $this->user->getCounty() || !$hasEtablissement) {
            $this->buildDepartementField($builder);
        }

        if (!$hasEtablissement) {
            $this->buildOrganizationTypeField($builder);
            $this->buildOrganizationField($builder);
            $this->buildOrganizationLabelField($builder);
        }
        if (null === $this->user->getProfileType()) {
            $this->buildProfileTypeField($builder);
        }

        return $fields;
    }

    /**
     * Retourne le champ Nom.
     *
     * @param FormBuilderInterface $builder
     */
    private function buildLastnameField(FormBuilderInterface $builder)
    {
        $builder->add('lastname', TextType::class, [
            'required' => true,
            'label' => 'Nom',
            'attr' => [
                'data-validation-engine' => 'validate[required]',
            ],
        ]);
    }

    /**
     * Retourne le champ Prénom.
     *
     * @param FormBuilderInterface $builder
     */
    private function buildFirstnameField(FormBuilderInterface $builder)
    {
        $builder->add('firstname', TextType::class, [
            'required' => true,
            'label' => 'Prénom',
            'attr' => [
                'data-validation-engine' => 'validate[required]',
            ],
        ]);
    }

    /**
     * Retourne le champ Région.
     *
     * @param FormBuilderInterface $builder
     */
    private function buildPhoneNumberField(FormBuilderInterface $builder)
    {
        $builder->add('phoneNumber', TextType::class, [
            'label' => 'user.phoneNumber',
            'required' => true,
            'attr' => [
                'data-validation-engine' => 'validate[required,minSize[14],maxSize[14]],custom[phone]',
                'data-mask' => '99 99 99 99 99',
            ],
        ]);
    }

    /**
     * Retourne le champ Région.
     *
     * @param FormBuilderInterface $builder
     */
    private function buildRegionField(FormBuilderInterface $builder)
    {
        $builder->add('region', EntityType::class, [
            'class' => 'HopitalNumeriqueReferenceBundle:Reference',
            'choices' => $this->referenceManager->findByCode('REGION'),
            'property' => 'libelle',
            'label' => 'user.region',
            'required' => false,
            'empty_value' => ' - ',
        ]);
    }

    /**
     * Retourne le champ Département.
     *
     * @param FormBuilderInterface $builder
     */
    private function buildDepartementField(FormBuilderInterface $builder)
    {
        $builder->add('county', EntityType::class, [
            'class' => 'HopitalNumeriqueReferenceBundle:Reference',
            'choices' => $this->referenceManager->findByCode('DEPARTEMENT'),
            'property' => 'libelle',
            'label' => 'user.county',
            'required' => false,
            'empty_value' => ' - ',
        ]);
    }

    /**
     * Retourne le champ Type d'ES. Obligatoire pour choisir un ES de rattachement.
     *
     * @param FormBuilderInterface $builder
     */
    private function buildOrganizationTypeField(FormBuilderInterface $builder)
    {
        $builder->add('organizationType', EntityType::class, [
            'class' => 'HopitalNumeriqueReferenceBundle:Reference',
            'choices' => $this->referenceManager->findByCode('CONTEXTE_TYPE_ES'),
            'property' => 'libelle',
            'label' => 'user.organizationType',
            'required' => false,
        ]);
    }

    /**
     * Retourne le champ ES de rattachement.
     *
     * @param FormBuilderInterface $builder
     */
    private function buildOrganizationField(FormBuilderInterface $builder)
    {
        $currentOrganization = $this->user->getOrganization();

        $organizationFormModifier = function (FormInterface $form, $full = false) use ($currentOrganization) {
            $fieldOptions = [
                'class'       => 'HopitalNumeriqueEtablissementBundle:Etablissement',
                'property'    => 'appellation',
                'required'    => false,
                'empty_value' => '-',
                'label'       => 'user.organization'
            ];

            if ($full) {
                $fieldOptions = array_merge(
                    $fieldOptions,
                    [
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('eta')->orderBy('eta.nom', 'ASC');
                        },
                    ]
                );
            } else {
                $fieldOptions['choices'] = is_null($currentOrganization)
                    ? []
                    : [$currentOrganization]
                ;
            }

            $form->add('organization', EntityType::class, $fieldOptions);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($organizationFormModifier) {
                $organizationFormModifier($event->getForm());
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($organizationFormModifier) {
                $organizationFormModifier($event->getForm(), true);
            }
        );

        $builder->add('organizationType', EntityType::class, [
            'class' => 'HopitalNumeriqueReferenceBundle:Reference',
            'choices' => $this->referenceManager->findByCode('CONTEXTE_TYPE_ES'),
            'property' => 'libelle',
            'required' => false,
            'empty_value' => '-',
            'label' => 'user.organizationType'
        ]);
    }

    /**
     * Retourne le champ Autre structure de rattachement.
     *
     * @param FormBuilderInterface $builder
     */
    private function buildOrganizationLabelField(FormBuilderInterface $builder)
    {
        $builder->add('organizationLabel', TextType::class, [
            'label' => 'user.organizationLabel',
            'required' => false,
            'attr' => [
                'maxlength' => 255,
            ],
        ]);
    }

    /**
     * Retourne le champ Profil de l'ES.
     *
     * @param FormBuilderInterface $builder
     */
    private function buildProfileTypeField(FormBuilderInterface $builder)
    {
        $builder->add('profileType', EntityType::class, [
            'class' => 'HopitalNumeriqueReferenceBundle:Reference',
            'choices' => $this->referenceManager->findByCode('CONTEXTE_METIER_INTERNAUTE'),
            'property' => 'libelle',
            'label' => 'user.profileType',
            'required' => true,
            'empty_value' => ' - ',
            'attr' => [
                'data-validation-engine' => 'validate[required]',
            ],
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['informations_type'])
            ->setAllowedValues([
                'informations_type' => [
                    self::TYPE_COMMUNAUTE_PRATIQUE,
                    self::TYPE_DEMANDE_INTERVENTION,
                ],
            ])
            ->setDefaults([
                'data_class' => 'HopitalNumerique\UserBundle\Entity\User',
            ])
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'nodevouser_user_informationsmanquantes';
    }
}
