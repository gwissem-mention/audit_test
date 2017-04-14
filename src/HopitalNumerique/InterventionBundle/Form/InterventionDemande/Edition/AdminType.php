<?php

namespace HopitalNumerique\InterventionBundle\Form\InterventionDemande\Edition;

use HopitalNumerique\InterventionBundle\Entity\InterventionInitiateur;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Manager\UserManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use HopitalNumerique\InterventionBundle\Form\InterventionDemandeType;
use Symfony\Component\Security\Core\SecurityContext;
use HopitalNumerique\InterventionBundle\Manager\InterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\InterventionDemandeManager as FormInterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\InterventionInitiateurManager as FormInterventionInitiateurManager;
use HopitalNumerique\InterventionBundle\Manager\Form\UserManager as FormUserManager;
use HopitalNumerique\InterventionBundle\Manager\Form\EtablissementManager as FormEtablissementManager;
use Symfony\Component\Validator\Validator\LegacyValidator;

/**
 * Formulaire d'édition d'une demande d'intervention spécifique dans l'administration.
 */
class AdminType extends InterventionDemandeType
{
    /**
     * @var UserManager UserManager
     */
    private $userManager;

    /**
     * @var FormInterventionInitiateurManager Manager Form\InterventionInitiateurManager
     */
    private $formInterventionInitiateurManager;

    /**
     * Constructeur du formulaire d'édition de demande d'intervention spécifique dans l'administration.
     *
     * @param SecurityContext                   $securityContext                   SecurityContext de l'application
     * @param LegacyValidator                   $validator                         LegacyValidator
     * @param UserManager                       $userManager
     * @param InterventionDemandeManager        $interventionDemandeManager        Manager InterventionDemande
     * @param FormInterventionDemandeManager    $formInterventionDemandeManager    Manager Form\InterventionDemande
     * @param FormInterventionInitiateurManager $formInterventionInitiateurManager Manager Form\InterventionInitiateur
     * @param FormUserManager                   $formUserManager                   Manager Form\User
     * @param FormEtablissementManager          $formEtablissementManager          Manager Form\Etablissement
     */
    public function __construct(
        SecurityContext $securityContext,
        $validator,
        UserManager $userManager,
        InterventionDemandeManager $interventionDemandeManager,
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
        $this->userManager                       = $userManager;
        $this->formInterventionInitiateurManager = $formInterventionInitiateurManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateCreation', DateType::class, [
                'label' => 'Date de création de la demande',
                'widget' => 'single_text',
                'format' => 'dd/MM/y',
                'required' => false,
                'read_only' => true,
            ])
            ->add('interventionInitiateur', EntityType::class, [
                'class' => InterventionInitiateur::class,
                'choice_label' => 'type',
                'label' => 'Initiateur de la demande',
                'required' => false,
                'disabled' => true,
            ])
            ->add('interventionEtat', EntityType::class, [
                'choices' => $this->formInterventionDemandeManager->getInterventionEtatsChoices(),
                'class' => Reference::class,
                'choice_label' => 'libelle',
                'label' => 'État actuel',
                'required' => true,
                'read_only' => false,
            ]);

        parent::buildForm($builder, $options);

        $builder
            ->add('referent', TextType::class) // Initialisé correctement après l'appel de la méthode mère
            ->add('cmsi', TextType::class, [
                'label' => 'CMSI',
                'mapped' => false,
                'data' => $builder->getData()->getCmsi()->getAppellation(),
                'required' => false,
                'read_only' => true,
            ])
            ->add('ambassadeur', EntityType::class) // Initialisé correctement après l'appel de la méthode mère
            ->add('cmsiDateChoix', DateType::class, [
                'label' => 'Date de refus ou acceptation CMSI',
                'widget' => 'single_text',
                'required' => false,
                'format' => 'dd/MM/y',
                'disabled' => true,
            ])
            ->add('ambassadeurDateChoix', DateType::class, [
                'label' => 'Date de refus ou acceptation ambassadeur',
                'widget' => 'single_text',
                'required' => false,
                'format' => 'dd/MM/y',
                'disabled' => true,
            ])
            ->add('referent', EntityType::class, [
                'choices' => $this->formUserManager->getReferentsChoices(),
                'class' => 'HopitalNumerique\UserBundle\Entity\User',
                'label' => 'Demandeur',
                'choice_label' => 'nomPrenom',
                'required' => true,
            ])

            ->add('ambassadeur', 'genemu_jqueryselect2_entity', [
                    'choices' => $this->formUserManager->getAmbassadeursChoices(),
                    'class' => 'HopitalNumerique\UserBundle\Entity\User',
                    'choice_label' => 'appellation',
                    'label' => 'Ambassadeur',
                    'required' => true,
                    'read_only' => false,
            ])
            ->add('cmsiCommentaire', TextareaType::class, [
                'label' => 'Commentaire CMSI',
                'required' => false,
                'read_only' => false,
            ])
            ->add('evaluationEtat', EntityType::class, [
                'choices' => $this->formInterventionDemandeManager->getEvaluationEtatsChoices(),
                'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                'choice_label' => 'libelle',
                'label' => 'État de l\'évaluation',
                'required' => false,
                'read_only' => false,
            ])
            ->add('remboursementEtat', EntityType::class, [
                'choices' => $this->formInterventionDemandeManager->getRemboursementEtatsChoices(),
                'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                'choice_label' => 'libelle',
                'label' => 'État du remboursement',
                'required' => false,
                'read_only' => false,
            ])
            ->add('refusMessage', TextareaType::class, [
                'label' => 'Motif de refus CMSI / Ambassadeur',
                'required' => false,
                'read_only' => false,
            ])
        ;
    }
}
