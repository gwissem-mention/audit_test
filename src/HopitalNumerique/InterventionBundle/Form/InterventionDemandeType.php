<?php

/**
 * Formulaire d'une demande d'intervention.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Form;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use HopitalNumerique\InterventionBundle\Manager\InterventionDemande;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use HopitalNumerique\InterventionBundle\Manager\InterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\InterventionDemandeManager as FormInterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\UserManager as FormUserManager;
use HopitalNumerique\InterventionBundle\Manager\Form\EtablissementManager as FormEtablissementManager;
use Symfony\Component\Validator\Validator\LegacyValidator;

/**
 * Formulaire d'une demande d'intervention.
 */
abstract class InterventionDemandeType extends AbstractType
{
    /**
     * @var array Pour la validation du formulaire
     */
    protected $_constraints = [];
    /**
     * @var FormInterventionDemandeManager Manager Form\InterventionDemande
     */
    protected $formInterventionDemandeManager;
    /**
     * @var FormUserManager Manager Form\User
     */
    protected $formUserManager;
    /**
     * @var FormEtablissementManager Manager Form\Etablissement
     */
    protected $formEtablissementManager;

    /**
     * @var User Utilisateur connecté
     */
    protected $utilisateurConnecte;
    /**
     * @var InterventionDemande La demande d'intervention ouverte
     */
    protected $interventionDemande;

    /**
     * InterventionDemandeType constructor.
     *
     * @param SecurityContext                $securityContext
     * @param                                $validator
     * @param InterventionDemandeManager     $interventionDemandeManager
     * @param FormInterventionDemandeManager $formInterventionDemandeManager
     * @param FormUserManager                $formUserManager
     * @param FormEtablissementManager       $formEtablissementManager
     */
    public function __construct(
        SecurityContext $securityContext,
        $validator,
        InterventionDemandeManager $interventionDemandeManager,
        FormInterventionDemandeManager $formInterventionDemandeManager,
        FormUserManager $formUserManager,
        FormEtablissementManager $formEtablissementManager
    ) {
        $this->_constraints = $interventionDemandeManager->getConstraints($validator);
        $this->formInterventionDemandeManager = $formInterventionDemandeManager;
        $this->formUserManager = $formUserManager;
        $this->formEtablissementManager = $formEtablissementManager;

        $this->utilisateurConnecte = $securityContext->getToken()->getUser();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->interventionDemande = $options['interventionDemande'];

        $reponseCourante = $builder->getData();
        $etablissementFieldOptions = [
            'class'        => 'HopitalNumeriqueEtablissementBundle:Etablissement',
            'choice_label' => 'appellation',
            'required'     => false,
            'label'        => 'Rattacher d\'autres établissements à ma demande, parmi',
            'multiple'     => true,
            'empty_value'  => '-',
            'attr'         => ['class' => 'ajax-select2-list hopitalnumerique_interventionbundle_interventiondemande_etablissements', 'data-url' => '/etablissement/load/'],
            'data'         => is_null($reponseCourante) ? null : $reponseCourante->getEtablissements(),
        ];

        $builder
            ->add('ambassadeur', EntityType::class, [
                'choices' => $this->formUserManager->getAmbassadeursChoices($this->utilisateurConnecte->getRegion()),
                'class' => 'HopitalNumerique\UserBundle\Entity\User',
                'property' => 'appellation',
                'label' => 'Ambassadeur',
                'required' => true,
                'read_only' => true,
            ])
            ->add('interventionType', EntityType::class, [
                'choices' => $this->formInterventionDemandeManager->getInterventionTypesChoices(),
                'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                'property' => 'libelle',
                'label' => 'Type d\'intervention souhaitée',
                'empty_value' => '-',
                'required' => true,
                'attr' => ['class' => $this->_constraints['interventionType']['class']],
            ])
            ->add('region', EntityType::class, [
                'label' => 'Région des établissements',
                'choices' => $this->formUserManager->getRegionsChoices(),
                'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                'property' => 'libelle',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'hopitalnumerique_interventionbundle_interventiondemande_region'],
                'data' => $this->utilisateurConnecte->getRegion(),
            ])
            ->add('email', TextType::class, [
                'label' => 'Adresse mail',
                'required' => true,
                'attr' => ['class' => $this->_constraints['email']['class']],
                'data' => is_null($options['interventionDemande']->getEmail())
                    ? $this->utilisateurConnecte->getEmail()
                    : $options['interventionDemande']->getEmail(),
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'required' => true,
                'attr' => [
                    'class' => $this->_constraints['telephone']['class'],
                    'data-mask' => $this->_constraints['telephone']['mask'],
                ],
                'data' => is_null($options['interventionDemande']->getTelephone())
                    ? $this->utilisateurConnecte->getTelephoneDirect()
                    : $options['interventionDemande']->getTelephone(),
            ])
            ->add('etablissements', EntityType::class, $etablissementFieldOptions)
            ->add('referent', EntityType::class, [
                'choices' => $this->formUserManager->getReferentsChoices(),
                'class' => 'HopitalNumerique\UserBundle\Entity\User',
                'label' => 'Demandeur',
                'required' => true,
                'attr' => [
                    'class' => 'hopitalnumerique_interventionbundle_interventiondemande_referent '
                               . $this->_constraints['referent']['class'],
                ],
            ])
            ->add('autresEtablissements', TextareaType::class, [
                'label' => 'Attacher d\'autres établissements à ma demande',
                'required' => false,
            ])
            ->add('objets', EntityType::class, [
                'choices' => $this->formInterventionDemandeManager->getObjetsChoices(),
                'label' => 'Ma sollicitation porte sur la/les production(s) ANAP suivante(s)',
                'class' => 'HopitalNumeriqueObjetBundle:Objet',
                'property' => 'titre',
                'multiple' => true,
                'required' => false,
                'attr' => ['class' => 'hopitalnumerique_interventionbundle_interventiondemande_objets'],
            ])
            ->add('connaissances', 'genemu_jqueryselect2_entity', [
                'label' => 'Ma sollicitation porte sur la/les connaissances(s) métier(s) suivante(s)',
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'multiple' => true,
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                        ->where('ref.code = :code')
                        ->leftJoin('ref.etat', 'etat', Expr\Join::WITH, 'etat.id = 3')
                        ->innerJoin('ref.parents', 'parent', Expr\Join::WITH, 'parent.id = :idParent')
                        ->setParameters([
                            'code' => 'PERIMETRE_FONCTIONNEL_DOMAINES_FONCTIONNELS',
                            'idParent' => 221,
                        ])
                        ->orderBy('ref.order', 'ASC')
                    ;
                },
            ])
            ->add('connaissancesSI', 'genemu_jqueryselect2_entity', [
                'label' => 'Ma sollicitation porte sur la/les connaissance(s) SI suivante(s)',
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'multiple' => true,
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('ref')
                        ->where('ref.code = :code')
                        ->leftJoin('ref.etat', 'etat')
                            ->andWhere('etat.id = 3')
                        ->setParameter('code', 'CONNAISSANCES_AMBASSADEUR_SI')
                        ->orderBy('ref.order', 'ASC')
                    ;
                },
            ])
            ->add('objetsAutres', TextareaType::class, [
                'label' => 'Ma sollicitation porte sur une autre production / un autre thème',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description succincte de mon projet',
                'required' => false,
            ])
            ->add('difficulteDescription', TextareaType::class, [
                'label' => 'Description de ma difficulté',
                'required' => false,
            ])
            ->add('champLibre', TextareaType::class, [
                'label' => 'Champ libre',
                'required' => false,
            ])
            ->add('rdvInformations', TextareaType::class, [
                'label' => 'Informations pour la prise de rendez-vous (échéance, disponibilités, etc)',
                'required' => false,
            ])
            ->add('cmsiCommentaire', TextareaType::class, [
                'label' => 'Commentaire CMSI',
                'required' => false,
                'read_only' => true,
            ])
        ;

        $etablissementMultipleFormModifier = function (
            FormInterface $form,
            $full = false
        ) use (
            $reponseCourante, $etablissementFieldOptions
        ) {

            if ($full) {
                $etablissementFieldOptions = array_merge(
                    $etablissementFieldOptions,
                    [
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('eta')->orderBy('eta.nom', 'ASC');
                        },
                    ]
                );
            } else {
                $etablissementFieldOptions['choices'] = is_null($reponseCourante) || is_null($reponseCourante->getEtablissements())
                    ? []
                    : $reponseCourante->getEtablissements()
                ;
            }

            $form->add('etablissements', EntityType::class, $etablissementFieldOptions);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($etablissementMultipleFormModifier) {
                $etablissementMultipleFormModifier($event->getForm());
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($etablissementMultipleFormModifier) {
                $etablissementMultipleFormModifier($event->getForm(), true);
            }
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\InterventionBundle\Entity\InterventionDemande',
            'interventionDemande' => null,
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_interventionbundle_interventiondemande';
    }
}
