<?php
namespace HopitalNumerique\UserBundle\Form\Type\User;

use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class InformationsManquantesType extends AbstractType
{
    /**
     * @var integer Type Communauté de pratiques
     */
    const TYPE_COMMUNAUTE_PRATIQUE = 1;


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


    /**
     * Constructeur.
     */
    public function __construct(TokenStorageInterface $tokenStorage, RouterInterface $router, RequestStack $requestStack, ReferenceManager $referenceManager)
    {
        $this->router = $router;
        $this->referenceManager = $referenceManager;

        $this->user = (null !== $tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null);
        $this->request = $requestStack->getCurrentRequest();
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $informationsType = $options['informations_type'];

        $builder->setAction($this->router->generate('hopital_numerique_account_informationsmanquantes_save', ['informationsType' => $informationsType]));
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
        foreach ($this->getFields($informationsType) as $field => $configuration) {
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
    private function getFields($informationsType)
    {
        switch ($informationsType) {
            case self::TYPE_COMMUNAUTE_PRATIQUE:
                return $this->getCommunautePratiqueFields();
            default:
                throw new \Exception('Type non reconnu pour le formulaire des informations manquantes.');
        }
    }

    /**
     * Retourne les champs du formulaire pour l'inscription à la communauté de pratiques.
     *
     * @return array Champs
     */
    private function getCommunautePratiqueFields()
    {
        $fields = [];

        if (null === $this->user->getRegion() || null === $this->user->getDepartement()) {
            $fields['region'] = [
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
            $fields['departement'] = [
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
        if (null === $this->user->getEtablissementRattachementSante() && null === $this->user->getAutreStructureRattachementSante() && null === $this->user->getNomStructure()) {
            $fields['etablissementRattachementSante'] = [
                'type' => 'entity',
                'options' => [
                    'class' => 'HopitalNumeriqueEtablissementBundle:Etablissement',
                    'property' => 'usersAffichage',
                    'label' => 'user.etablissementRattachementSante',
                    'required' => false
                ]
            ];
            $fields['autreStructureRattachementSante'] = [
                'type' => 'text',
                'options' => [
                    'label' => 'user.autreStructureRattachementSante',
                    'required' => false,
                    'attr' => [
                        'maxlength' => 255
                    ]
                ]
            ];
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
            $fields['profilEtablissementSante'] = [
                'type' => 'entity',
                'options' => [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices' => $this->referenceManager->findByCode('CONTEXTE_METIER_INTERNAUTE'),
                    'property' => 'libelle',
                    'label' => 'user.profilEtablissementSante',
                    'required' => true,
                    'attr' => [
                        'data-validation-engine' => 'validate[required]'
                    ]
                ]
            ];
        }
        if (0 == count($this->user->getTypeActivite())) {
            $fields['typeActivite'] = [
                'type' => 'entity',
                'options' => [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices' => $this->referenceManager->findByCode('CONTEXTE_SPECIALITE_ES'),
                    'property' => 'libelle',
                    'label' => 'user.typeActivite',
                    'multiple' => true,
                    'required' => true,
                    'attr' => [
                        'data-validation-engine' => 'validate[required]'
                    ]
                ]
            ];
        }
        if (null === $this->user->getFonctionDansEtablissementSanteReferencement()) {
            $fields['fonctionDansEtablissementSanteReferencement'] = [
                'type' => 'entity',
                'options' => [
                    'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                    'choices' => $this->referenceManager->findByCode('CONTEXTE_FONCTION_INTERNAUTE'),
                    'property' => 'libelle',
                    'label' => 'user.fonctionDansEtablissementSanteReferencement',
                    'empty_value' => ' - ',
                    'required' => true,
                    'attr' => [
                        'data-validation-engine' => 'validate[required]'
                    ]
                ]
            ];
        }

        return $fields;
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
                    self::TYPE_COMMUNAUTE_PRATIQUE
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
