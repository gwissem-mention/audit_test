<?php

namespace HopitalNumerique\InterventionBundle\Form\InterventionDemande;

use HopitalNumerique\EtablissementBundle\Entity\Etablissement;
use HopitalNumerique\InterventionBundle\Manager\Form\EtablissementManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use HopitalNumerique\InterventionBundle\Form\InterventionDemandeType;
use Symfony\Component\Security\Core\SecurityContext;
use HopitalNumerique\InterventionBundle\Manager\InterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\InterventionDemandeManager as FormInterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\InterventionInitiateurManager as FormInterventionInitiateurManager;
use HopitalNumerique\InterventionBundle\Manager\Form\UserManager as FormUserManager;
use HopitalNumerique\InterventionBundle\Manager\Form\EtablissementManager as FormEtablissementManager;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;
use Symfony\Component\Validator\Validator\LegacyValidator;

/**
 * Formulaire de création d'une demande d'intervention spécifique dans l'administration.
 */
class AdminType extends InterventionDemandeType
{
    /**
     * @var ObjetManager Manager de Objet
     */
    private $objetManager;
    /**
     * @var FormInterventionInitiateurManager Manager Form\InterventionInitiateurManager
     */
    private $formInterventionInitiateurManager;

    /**
     * Constructeur du formulaire de création de demande d'intervention spécifique dans l'administration.
     *
     * @param SecurityContext                   $securityContext                   SecurityContext de l'application
     * @param LegacyValidator                   $validator                         LegacyValidator
     * @param InterventionDemandeManager        $interventionDemandeManager        Manager InterventionDemande
     * @param ObjetManager                      $objetManager                      Manager Objet
     * @param FormInterventionDemandeManager    $formInterventionDemandeManager    Manager Form\InterventionDemande
     * @param FormInterventionInitiateurManager $formInterventionInitiateurManager Manager Form\InterventionInitiateur
     * @param FormUserManager                   $formUserManager                   Manager Form\User
     * @param EtablissementManager              $formEtablissementManager          Manager Form\Etablissement
     */
    public function __construct(
        SecurityContext $securityContext,
        $validator,
        InterventionDemandeManager $interventionDemandeManager,
        ObjetManager $objetManager,
        FormInterventionDemandeManager $formInterventionDemandeManager,
        FormInterventionInitiateurManager $formInterventionInitiateurManager,
        FormUserManager $formUserManager,
        FormEtablissementManager $formEtablissementManager
    ) {
        parent::__construct(
            $securityContext,
            $validator,
            $interventionDemandeManager,
            $formInterventionDemandeManager,
            $formUserManager,
            $formEtablissementManager
        );

        $this->objetManager                      = $objetManager;
        $this->formInterventionInitiateurManager = $formInterventionInitiateurManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $reponseCourante = $builder->getData();

        $builder
            ->add('interventionInitiateur', EntityType::class, [
                'choices' => $this->formInterventionInitiateurManager->getInterventionInitiateursChoices(),
                'class' => 'HopitalNumerique\InterventionBundle\Entity\InterventionInitiateur',
                'choice_label' => 'type',
                'label' => 'Initiateur de la demande',
                'required' => false,
                'read_only' => true,
                'disabled' => true,
            ])
            ->add('interventionEtat', EntityType::class, [
                'choices' => $this->formInterventionDemandeManager->getInterventionEtatsChoices(),
                'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                'choice_label' => 'libelle',
                'label' => 'État actuel',
                'required' => true,
                'read_only' => true,
                'disabled' => true,
            ]);
        parent::buildForm($builder, $options);
        $builder
            ->add('region', EntityType::class, [
                'label' => 'Région des établissements',
                'choices' => $this->formUserManager->getRegionsChoices(),
                'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                'empty_value' => '-',
                'choice_label' => 'libelle',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'hopitalnumerique_interventionbundle_interventiondemande_region'],
            ])
            ->add('etablissements', EntityType::class, [
                'class'        => Etablissement::class,
                'choice_label' => 'appellation',
                'required'     => false,
                'label'        => 'Rattacher d\'autres établissements à ma demande, parmi',
                'multiple'     => true,
                'empty_value'  => '-',
                'attr'         => ['class' => 'ajax-list-select2 hopitalnumerique_interventionbundle_interventiondemande_etablissements', 'data-url' => '/etablissement/load/'],
                'data'         => is_null($reponseCourante) ? null : $reponseCourante->getEtablissements(),
            ])
            ->add('ambassadeur', EntityType::class, [
                'choices' => $this->formUserManager->getAmbassadeursChoices(),
                'class' => 'HopitalNumerique\UserBundle\Entity\User',
                'empty_value' => '-',
                'choice_label' => 'appellation',
                'label' => 'Ambassadeur',
                'required' => true,
                'read_only' => false,
                'attr' => ['class' => 'hopitalnumerique_interventionbundle_interventiondemande_ambassadeur'],
            ])
            ->add('referent', EntityType::class, [
                'choices' => $this->formUserManager->getReferentsChoices(),
                'class' => 'HopitalNumerique\UserBundle\Entity\User',
                'label' => 'Demandeur',
                'choice_label' => 'nomPrenom',
                'required' => true,
            ])
            ->add('objets', EntityType::class, [
                'choices' => $this->objetManager->getProductionsActive(),
                'label' => 'Ma sollicitation porte sur la/les production(s) ANAP suivante(s)',
                'class' => 'HopitalNumeriqueObjetBundle:Objet',
                'choice_label' => 'titre',
                'multiple' => true,
                'required' => false,
                'attr' => ['class' => 'hopitalnumerique_interventionbundle_interventiondemande_objets'],
            ])
            ->add('cmsiCommentaire', TextareaType::class, [
                'label' => 'Commentaire CMSI',
                'required' => false,
                'read_only' => false,
            ])
        ;
    }
}
