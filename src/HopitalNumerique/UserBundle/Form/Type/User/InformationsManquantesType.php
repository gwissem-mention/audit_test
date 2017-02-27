<?php

namespace HopitalNumerique\UserBundle\Form\Type\User;

use HopitalNumerique\EtablissementBundle\Manager\EtablissementManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
     * @var \Symfony\Component\Routing\RouterInterface Router
     */
    private $router;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager ReferenceManager
     */
    private $referenceManager;

    /**
     * @var \HopitalNumerique\UserBundle\Entity\User Utilisateur connecté
     */
    private $user;

    /**
     * @var \Symfony\Component\HttpFoundation\Request Request
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
            ->add('informationsType', 'hidden', [
                'mapped' => false,
                'data' => $informationsType,
            ])
            ->add('redirection', 'hidden', [
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
     * @param string $informationsType Type des informations
     *
     * @return array Champs
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
     * @return array Champs
     */
    private function getCommunautePratiqueFields(FormBuilderInterface $builder)
    {
        $fields = [];

        $displayUserNomPrenom =
            empty(trim($this->user->getNom()))
            || empty(trim($this->user->getPrenom()))
        ;

        $displayEtablissementNom =
            null === $this->user->getEtablissementRattachementSante()
            && null === $this->user->getAutreStructureRattachementSante()
            && null === $this->user->getNomStructure()
        ;

        $displayRegionDepartement =
            (null === $this->user->getRegion() || null === $this->user->getDepartement())
            || $displayEtablissementNom
        ;

        if ($displayUserNomPrenom) {
            $this->buildNomField($builder);
            $this->buildPrenomField($builder);
        }

        if ($displayRegionDepartement) {
            $this->buildRegionField($builder);
            $this->buildDepartementField($builder);

            if ($displayEtablissementNom) {
                $this->buildStatutEtablissementSanteField($builder);
                $this->buildEtablissementRattachementSanteField($builder);
                $this->buildAutreStructureRattachementSanteField($builder);
                $builder->add('nomStructure', 'text', [
                    'label' => 'user.nomStructure',
                    'required' => false,
                    'attr' => [
                        'maxlength' => 255,
                    ],
                ]);
            }
        }

        if (null === $this->user->getProfilEtablissementSante()) {
            $this->buildProfilEtablissementSanteField($builder);
        }

        if (null === $this->user->getFonctionDansEtablissementSanteReferencement() && null === $this->user->getFonctionStructure()) {
            $builder->add('fonctionDansEtablissementSanteReferencement', 'entity', [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('CONTEXTE_FONCTION_INTERNAUTE'),
                'property' => 'libelle',
                'label' => 'user.fonctionDansEtablissementSanteReferencement',
                'empty_value' => ' - ',
                'required' => false,
            ]);

            $builder->add('fonctionStructure', 'text', [
                'label' => 'user.fonctionStructure',
                'required' => false,
                'attr' => [
                    'maxlength' => 255,
                ],
            ]);
        }

        return $fields;
    }

    /**
     * Retourne les champs du formulaire pour une demande d'intervention.
     *
     * @return array Champs
     */
    private function getDemandeInterventionFields(FormBuilderInterface $builder)
    {
        $fields = [];

        $hasEtablissement = (null !== $this->user->getEtablissementRattachementSante() || null !== $this->user->getAutreStructureRattachementSante());

        if (null === $this->user->getTelephoneDirect()) {
            $this->buildTelephoneDirectField($builder);
        }
        if (null === $this->user->getRegion() || !$hasEtablissement) {
            $this->buildRegionField($builder);
        }
        if (null === $this->user->getDepartement() || !$hasEtablissement) {
            $this->buildDepartementField($builder);
        }

        if (!$hasEtablissement) {
            $this->buildStatutEtablissementSanteField($builder);
            $this->buildEtablissementRattachementSanteField($builder);
            $this->buildAutreStructureRattachementSanteField($builder);
        }
        if (null === $this->user->getProfilEtablissementSante()) {
            $this->buildProfilEtablissementSanteField($builder);
        }

        return $fields;
    }

    /**
     * Retourne le champ Nom.
     *
     * @return array Champ
     */
    private function buildNomField(FormBuilderInterface $builder)
    {
        $builder->add('nom', 'text', [
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
     * @return array Champ
     */
    private function buildPrenomField(FormBuilderInterface $builder)
    {
        $builder->add('prenom', 'text', [
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
     * @return array Champ
     */
    private function buildTelephoneDirectField(FormBuilderInterface $builder)
    {
        $builder->add('telephoneDirect', 'text', [
            'label' => 'user.telephoneDirect',
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
     * @return array Champ
     */
    private function buildRegionField(FormBuilderInterface $builder)
    {
        $builder->add('region', 'entity', [
            'class' => 'HopitalNumeriqueReferenceBundle:Reference',
            'choices' => $this->referenceManager->findByCode('REGION'),
            'property' => 'libelle',
            'label' => 'user.region',
            'required' => true,
            'empty_value' => ' - ',
            'attr' => [
                'data-validation-engine' => 'validate[required]',
            ],
        ]);
    }

    /**
     * Retourne le champ Département.
     *
     * @return array Champ
     */
    private function buildDepartementField(FormBuilderInterface $builder)
    {
        $builder->add('departement', 'entity', [
            'class' => 'HopitalNumeriqueReferenceBundle:Reference',
            'choices' => $this->referenceManager->findByCode('DEPARTEMENT'),
            'property' => 'libelle',
            'label' => 'user.departement',
            'required' => true,
            'empty_value' => ' - ',
            'attr' => [
                'data-validation-engine' => 'validate[required]',
            ],
        ]);
    }

    /**
     * Retourne le champ Type d'ES. Obligatoire pour choisir un ES de rattachement.
     *
     * @return array Champ
     */
    private function buildStatutEtablissementSanteField(FormBuilderInterface $builder)
    {
        $builder->add('statutEtablissementSante', 'entity', [
            'class' => 'HopitalNumeriqueReferenceBundle:Reference',
            'choices' => $this->referenceManager->findByCode('CONTEXTE_TYPE_ES'),
            'property' => 'libelle',
            'label' => 'user.statutEtablissementSante',
            'required' => false,
        ]);
    }

    /**
     * Retourne le champ ES de rattachement.
     *
     * @return array Champ
     */
    private function buildEtablissementRattachementSanteField(FormBuilderInterface $builder)
    {
        $etablissementRattachementSanteModifier = function (FormInterface $form, $data) {
            $form->add('etablissementRattachementSante', 'choice', [
                'required' => false,
                'label' => 'user.etablissementRattachementSante',
                'empty_value' => ' - ',
                'attr' => ['class' => 'etablissement_sante'],
                'choices' => $data,
                'choices_as_values' => true,
                'choice_value' => 'id',
                'choice_label' => 'nom',
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($etablissementRattachementSanteModifier) {
                /** @var User $data */
                $data = $event->getData();
                $form = $event->getForm();

                $list = $this->etablissementManager->findBy([
                    'departement' => $data->getDepartement(),
                    'typeOrganisme' => $data->getStatutEtablissementSante(),
                ]);
                $etablissementRattachementSanteModifier($form, $list);
            }
        );

        $builder->get('statutEtablissementSante')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($etablissementRattachementSanteModifier) {
                $form = $event->getForm()->getParent();
                $status = $event->getForm()->getData();

                $list = $this->etablissementManager->findBy([
                    'departement' => $event->getForm()->getParent()->get('departement')->getData(),
                    'typeOrganisme' => $status,
                ]);

                $etablissementRattachementSanteModifier($form, $list);
            }
        );
    }

    /**
     * Retourne le champ Autre structure de rattachement.
     *
     * @return array Champ
     */
    private function buildAutreStructureRattachementSanteField(FormBuilderInterface $builder)
    {
        $builder->add('autreStructureRattachementSante', 'text', [
            'label' => 'user.autreStructureRattachementSante',
            'required' => false,
            'attr' => [
                'maxlength' => 255,
            ],
        ]);
    }

    /**
     * Retourne le champ Profil de l'ES.
     *
     * @return array Champ
     */
    private function buildProfilEtablissementSanteField(FormBuilderInterface $builder)
    {
        $builder->add('profilEtablissementSante', 'entity', [
            'class' => 'HopitalNumeriqueReferenceBundle:Reference',
            'choices' => $this->referenceManager->findByCode('CONTEXTE_METIER_INTERNAUTE'),
            'property' => 'libelle',
            'label' => 'user.profilEtablissementSante',
            'required' => true,
            'empty_value' => ' - ',
            'attr' => [
                'data-validation-engine' => 'validate[required]',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'nodevouser_user_informationsmanquantes';
    }
}
