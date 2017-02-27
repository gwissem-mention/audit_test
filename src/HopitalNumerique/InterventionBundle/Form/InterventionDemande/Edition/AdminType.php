<?php
/**
 * Formulaire d'édition d'une demande d'intervention dans l'administration.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */

namespace HopitalNumerique\InterventionBundle\Form\InterventionDemande\Edition;

use HopitalNumerique\UserBundle\Manager\UserManager;
use Symfony\Component\Form\FormBuilderInterface;
use HopitalNumerique\InterventionBundle\Form\InterventionDemandeType;
use Symfony\Component\Security\Core\SecurityContext;
use HopitalNumerique\InterventionBundle\Manager\InterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\InterventionDemandeManager as FormInterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\InterventionInitiateurManager as FormInterventionInitiateurManager;
use HopitalNumerique\InterventionBundle\Manager\Form\UserManager as FormUserManager;
use HopitalNumerique\InterventionBundle\Manager\Form\EtablissementManager as FormEtablissementManager;

/**
 * Formulaire d'édition d'une demande d'intervention spécifique dans l'administration.
 */
class AdminType extends InterventionDemandeType
{
    /**
     * @var \HopitalNumerique\UserBundle\Manager\UserManager UserManager
     */
    private $userManager;

    /**
     * @var \HopitalNumerique\InterventionBundle\Manager\Form\InterventionInitiateurManager Manager Form\InterventionInitiateurManager
     */
    private $formInterventionInitiateurManager;

    /**
     * Constructeur du formulaire d'édition de demande d'intervention spécifique dans l'administration.
     *
     * @param \Symfony\Component\Security\Core\SecurityContext                                $securityContext                   SecurityContext de l'application
     * @param \Symfony\Component\Validator\Validator\LegacyValidator                          $validator                         LegacyValidator
     * @param \HopitalNumerique\InterventionBundle\Manager\InterventionDemandeManager         $interventionDemandeManager        Manager InterventionDemande
     * @param \HopitalNumerique\InterventionBundle\Manager\Form\InterventionDemandeManager    $formInterventionDemandeManager    Manager Form\InterventionDemande
     * @param \HopitalNumerique\InterventionBundle\Manager\Form\InterventionInitiateurManager $formInterventionInitiateurManager Manager Form\InterventionInitiateur
     * @param \HopitalNumerique\InterventionBundle\Manager\Form\UserManager                   $formUserManager                   Manager Form\User
     * @param \HopitalNumerique\InterventionBundle\Manager\Form\EtablissementManager          $formEtablissementManager          Manager Form\Etablissement
     */
    public function __construct(SecurityContext $securityContext, $validator, UserManager $userManager, InterventionDemandeManager $interventionDemandeManager, FormInterventionDemandeManager $formInterventionDemandeManager, FormInterventionInitiateurManager $formInterventionInitiateurManager, FormUserManager $formUserManager, FormEtablissementManager $formEtablissementManager)
    {
        parent::__construct($securityContext, $validator, $interventionDemandeManager, $formInterventionDemandeManager, $formUserManager, $formEtablissementManager);
        $this->userManager = $userManager;
        $this->formInterventionInitiateurManager = $formInterventionInitiateurManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->interventionDemande = $options['interventionDemande'];

        $builder
            ->add('dateCreation', 'text', [
                'label' => 'Date de création de la demande',
                'mapped' => false,
                'data' => $this->interventionDemande->getDateCreation()->format('d/m/Y'),
                'required' => false,
                'read_only' => true,
            ])
            ->add('interventionInitiateur', 'entity', [
                'choices' => $this->formInterventionInitiateurManager->getInterventionInitiateursChoices(),
                'class' => 'HopitalNumerique\InterventionBundle\Entity\InterventionInitiateur',
                'property' => 'type',
                'label' => 'Initiateur de la demande',
                'required' => false,
                'read_only' => true,
            ])
            ->add('interventionEtat', 'entity', [
                'choices' => $this->formInterventionDemandeManager->getInterventionEtatsChoices(),
                'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                'property' => 'libelle',
                'label' => 'État actuel',
                'required' => true,
                'read_only' => false,
            ]);

        parent::buildForm($builder, $options);

        $builder
            ->add('referent', 'text') // Initialisé correctement après l'appel de la méthode mère
            ->add('cmsi', 'text', [
                'label' => 'CMSI',
                'mapped' => false,
                'data' => $this->interventionDemande->getCmsi()->getAppellation(),
                'required' => false,
                'read_only' => true,
            ])
            ->add('ambassadeur', 'entity') // Initialisé correctement après l'appel de la méthode mère
            ->add('cmsiDateChoix', 'text', [
                'label' => 'Date de refus ou acceptation CMSI',
                'mapped' => false,
                'data' => ($this->interventionDemande->getCmsiDateChoix() != null ? $this->interventionDemande->getCmsiDateChoix()->format('d/m/Y') : ''),
                'required' => false,
                'read_only' => true,
            ])
            ->add('ambassadeurDateChoix', 'text', [
                'label' => 'Date de refus ou acceptation ambassadeur',
                'mapped' => false,
                'data' => ($this->interventionDemande->getAmbassadeurDateChoix() != null ? $this->interventionDemande->getAmbassadeurDateChoix()->format('d/m/Y') : ''),
                'required' => false,
                'read_only' => true,
            ])
            ->add('referent', 'entity', [
                'choices' => $this->formUserManager->getReferentsChoices(),
                'class' => 'HopitalNumerique\UserBundle\Entity\User',
                'label' => 'Demandeur',
                'property' => 'nomPrenom',
                'required' => true,
            ])

            ->add('ambassadeur', 'genemu_jqueryselect2_entity', [
                    'choices' => $this->formUserManager->getAmbassadeursChoices(),
                    'class' => 'HopitalNumerique\UserBundle\Entity\User',
                    'property' => 'appellation',
                    'label' => 'Ambassadeur',
                    'required' => true,
                    'read_only' => false,
            ])
            ->add('cmsiCommentaire', 'textarea', [
                'label' => 'Commentaire CMSI',
                'required' => false,
                'read_only' => false,
            ])
            ->add('evaluationEtat', 'entity', [
                'choices' => $this->formInterventionDemandeManager->getEvaluationEtatsChoices(),
                'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                'property' => 'libelle',
                'label' => 'État de l\'évaluation',
                'required' => false,
                'read_only' => false,
            ])
            ->add('remboursementEtat', 'entity', [
                'choices' => $this->formInterventionDemandeManager->getRemboursementEtatsChoices(),
                'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                'property' => 'libelle',
                'label' => 'État du remboursement',
                'required' => false,
                'read_only' => false,
            ])
            ->add('refusMessage', 'textarea', [
                'label' => 'Motif de refus CMSI / Ambassadeur',
                'required' => false,
                'read_only' => false,
            ])
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_interventionbundle_interventiondemande_edition_admin';
    }
}
