<?php
namespace HopitalNumerique\UserBundle\Form\Type\User;

use HopitalNumerique\EtablissementBundle\Manager\EtablissementManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
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
     * @var integer Type Communauté de pratique
     */
    const TYPE_COMMUNAUTE_PRATIQUE = 1;

    /**
     * @var integer Type Demande d'intervention d'un ambssadeur
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
     * @param TokenStorageInterface $tokenStorage
     * @param RouterInterface $router
     * @param RequestStack $requestStack
     * @param ReferenceManager $referenceManager
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
                'data' => $informationsType
            ])
            ->add('redirection', 'hidden', [
                'mapped' => false,
                'data' => $this->request->server->get('REDIRECT_URL')
            ])
        ;
        foreach ($this->getFields($builder, $informationsType) as $field => $configuration) {
            $builder
                ->add($field, $configuration['type'], $configuration['options'])
            ;
        }
    }

    /**
     * Retourne les champs du formulaire.
     *
     * @param string $informationsType Type des informations
     * @return array Champs
     */
    private function getFields(FormBuilderInterface $builder, $informationsType)
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
            $fields['nom'] = $this->getNomField();
            $fields['prenom'] = $this->getPrenomField();
        }

        if ($displayRegionDepartement) {
            $fields['region'] = $this->getRegionField();
            $fields['departement'] = $this->getDepartementField();
        }
        if ($displayEtablissementNom) {
            $fields['statutEtablissementSante'] = $this->getStatutEtablissementSanteField();
            $this->getEtablissementRattachementSanteField($builder);
            $fields['autreStructureRattachementSante'] = $this->getAutreStructureRattachementSanteField();
            $fields['nomStructure'] = [
                'type' => 'text',
                'options' => [
                    'label' => 'user.nomStructure',
                    'required' => false,
                    'attr' => [
                        'maxlength' => 255
                    ]
                ]
            ];
        }
        if (null === $this->user->getProfilEtablissementSante()) {
            $fields['profilEtablissementSante'] = $this->getProfilEtablissementSanteField();
        }
        if (null === $this->user->getFonctionDansEtablissementSanteReferencement() && null === $this->user->getFonctionStructure()) {
            $fields['fonctionDansEtablissementSanteReferencement'] = [
                'type' => 'entity',
                'options' => [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices' => $this->referenceManager->findByCode('CONTEXTE_FONCTION_INTERNAUTE'),
                    'property' => 'libelle',
                    'label' => 'user.fonctionDansEtablissementSanteReferencement',
                    'empty_value' => ' - ',
                    'required' => false
                ]
            ];
            $fields['fonctionStructure'] = [
                'type' => 'text',
                'options' => [
                    'label' => 'user.fonctionStructure',
                    'required' => false,
                    'attr' => [
                        'maxlength' => 255
                    ]
                ]
            ];
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
            $fields['telephoneDirect'] = $this->getTelephoneDirectField();
        }
        if (null === $this->user->getRegion() || !$hasEtablissement) {
            $fields['region'] = $this->getRegionField();
        }
        if (null === $this->user->getDepartement() || !$hasEtablissement) {
            $fields['departement'] = $this->getDepartementField();
        }

        if (!$hasEtablissement) {
            $fields['statutEtablissementSante'] = $this->getStatutEtablissementSanteField();
            $this->getEtablissementRattachementSanteField($builder);
            $fields['autreStructureRattachementSante'] = $this->getAutreStructureRattachementSanteField();
        }
        if (null === $this->user->getProfilEtablissementSante()) {
            $fields['profilEtablissementSante'] = $this->getProfilEtablissementSanteField();
        }

        return $fields;
    }

    /**
     * Retourne le champ Nom.
     *
     * @return array Champ
     */
    private function getNomField()
    {
        return [
            'type' => 'text',
            'options' => [
                'required'   => true,
                'label'      => 'Nom',
                'attr' => [
                    'data-validation-engine' => 'validate[required]'
                ]
            ]
        ];
    }

    /**
     * Retourne le champ Prénom.
     *
     * @return array Champ
     */
    private function getPrenomField()
    {
        return [
            'type' => 'text',
            'options' => [
                'required'   => true,
                'label'      => 'Prénom',
                'attr' => [
                    'data-validation-engine' => 'validate[required]'
                ]
            ]
        ];
    }


    /**
     * Retourne le champ Région.
     *
     * @return array Champ
     */
    private function getTelephoneDirectField()
    {
        return [
            'type' => 'text',
            'options' => [
                'label' => 'user.telephoneDirect',
                'required' => true,
                'attr' => [
                    'data-validation-engine' => 'validate[required,minSize[14],maxSize[14]],custom[phone]',
                    'data-mask' => '99 99 99 99 99'
                ]
            ]
        ];
    }

    /**
     * Retourne le champ Région.
     *
     * @return array Champ
     */
    private function getRegionField()
    {
        return [
            'type' => 'entity',
            'options' => [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('REGION'),
                'property' => 'libelle',
                'label' => 'user.region',
                'required' => true,
                'empty_value' => ' - ',
                'attr' => [
                    'data-validation-engine' => 'validate[required]'
                ]
            ]
        ];
    }

    /**
     * Retourne le champ Département.
     *
     * @return array Champ
     */
    private function getDepartementField()
    {
        return [
            'type' => 'entity',
            'options' => [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('DEPARTEMENT'),
                'property' => 'libelle',
                'label' => 'user.departement',
                'required' => true,
                'empty_value' => ' - ',
                'attr' => [
                    'data-validation-engine' => 'validate[required]'
                ]
            ]
        ];
    }

    /**
     * Retourne le champ Type d'ES. Obligatoire pour choisir un ES de rattachement.
     *
     * @return array Champ
     */
    private function getStatutEtablissementSanteField()
    {
        return [
            'type' => 'entity',
            'options' => [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('CONTEXTE_TYPE_ES'),
                'property' => 'libelle',
                'label' => 'user.statutEtablissementSante',
                'required' => false
            ]
        ];
    }

    /**
     * Retourne le champ ES de rattachement.
     *
     * @return array Champ
     */
    private function getEtablissementRattachementSanteField(FormBuilderInterface $builder)
    {
        $etablissementRattachementSanteModifier = function (FormInterface $form, $data) {
            $form->add('etablissementRattachementSante', 'choice', array(
                'required'      => false,
                'label'         => 'user.etablissementRattachementSante',
                'empty_value'   => ' - ',
                'attr'        => array('class' => 'etablissement_sante'),
                'choices' => $data,
                'choices_as_values' => true,
                'choice_value' => 'id',
                'choice_label' => 'nom',
            ));
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($etablissementRattachementSanteModifier) {
                /** @var User $data */
                $data = $event->getData();
                $form = $event->getForm();

                $list = $this->etablissementManager->findBy(array(
                    'departement'   => $data->getDepartement(),
                    'typeOrganisme' => $data->getStatutEtablissementSante()
                ));
                $etablissementRattachementSanteModifier($form, $list);
            }
        );

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($etablissementRattachementSanteModifier) {
                /** @var User $data */
                $data = $event->getData();
                $form = $event->getForm();

                $list = $this->etablissementManager->findBy(array(
                    'departement'   => $data->getDepartement(),
                    'typeOrganisme' => $data->getStatutEtablissementSante()
                ));

                $etablissementRattachementSanteModifier($form, $list);
            }
        );
    }

    /**
     * Retourne le champ Autre structure de rattachement.
     *
     * @return array Champ
     */
    private function getAutreStructureRattachementSanteField()
    {
        return [
            'type' => 'text',
            'options' => [
                'label' => 'user.autreStructureRattachementSante',
                'required' => false,
                'attr' => [
                    'maxlength' => 255
                ]
            ]
        ];
    }

    /**
     * Retourne le champ Profil de l'ES.
     *
     * @return array Champ
     */
    private function getProfilEtablissementSanteField()
    {
        return [
            'type' => 'entity',
            'options' => [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('CONTEXTE_METIER_INTERNAUTE'),
                'property' => 'libelle',
                'label' => 'user.profilEtablissementSante',
                'required' => true,
                'empty_value' => ' - ',
                'attr' => [
                    'data-validation-engine' => 'validate[required]'
                ]
            ]
        ];
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
                    self::TYPE_DEMANDE_INTERVENTION
                ]
            ])
            ->setDefaults([
                'data_class' => 'HopitalNumerique\UserBundle\Entity\User'
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
