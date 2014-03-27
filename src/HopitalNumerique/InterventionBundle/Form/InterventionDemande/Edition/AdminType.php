<?php
/**
 * Formulaire d'édition d'une demande d'intervention dans l'administration.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Form\InterventionDemande\Edition;

use Symfony\Component\Form\FormBuilderInterface;
use HopitalNumerique\InterventionBundle\Form\InterventionDemandeType;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Validator\Validator;
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
     * @var \HopitalNumerique\InterventionBundle\Manager\Form\InterventionInitiateurManager Manager Form\InterventionInitiateurManager
     */
    private $formInterventionInitiateurManager;
    
    /**
     * Constructeur du formulaire d'édition de demande d'intervention spécifique dans l'administration.
     *
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext SecurityContext de l'application
     * @param \Symfony\Component\Validator\Validator $validator Validator
     * @param \HopitalNumerique\InterventionBundle\Manager\InterventionDemandeManager $interventionDemandeManager Manager InterventionDemande
     * @param \HopitalNumerique\InterventionBundle\Manager\Form\InterventionDemandeManager $formInterventionDemandeManager Manager Form\InterventionDemande
     * @param \HopitalNumerique\InterventionBundle\Manager\Form\InterventionInitiateurManager $formInterventionInitiateurManager Manager Form\InterventionInitiateur
     * @param \HopitalNumerique\InterventionBundle\Manager\Form\UserManager $formUserManager Manager Form\User
     * @param \HopitalNumerique\InterventionBundle\Manager\Form\EtablissementManager $formEtablissementManager Manager Form\Etablissement
     * @return void
     */
    public function __construct(SecurityContext $securityContext, Validator $validator, InterventionDemandeManager $interventionDemandeManager, FormInterventionDemandeManager $formInterventionDemandeManager, FormInterventionInitiateurManager $formInterventionInitiateurManager, FormUserManager $formUserManager, FormEtablissementManager $formEtablissementManager)
    {
        parent::__construct($securityContext, $validator, $interventionDemandeManager, $formInterventionDemandeManager, $formUserManager, $formEtablissementManager);
        $this->formInterventionInitiateurManager = $formInterventionInitiateurManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->interventionDemande = $options['interventionDemande'];
        
        $builder
            ->add('interventionInitiateur', 'entity', array(
                'choices' => $this->formInterventionInitiateurManager->getInterventionInitiateursChoices(),
                'class' => 'HopitalNumerique\InterventionBundle\Entity\InterventionInitiateur',
                'property' => 'type',
                'label' => 'Initiateur de la demande',
                'required' => false,
                'read_only' => true
            ))
            ->add('dateCreation', 'text', array(
                'label' => 'Date',
                'mapped' => false,
                'data' => $this->interventionDemande->getDateCreation()->format('d/m/Y'),
                'required' => false,
                'read_only' => true
            ))
            ->add('interventionEtat', 'entity', array(
                'choices' => $this->formInterventionDemandeManager->getInterventionEtatsChoices(),
                'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                'property' => 'libelle',
                'label' => 'État actuel',
                'required' => true,
                'read_only' => false
            ))
            ->add('cmsi', 'text', array(
                'label' => 'CMSI',
                'mapped' => false,
                'data' => $this->interventionDemande->getCmsi()->getAppellation(),
                'required' => false,
                'read_only' => true
            ))
        ;
        parent::buildForm($builder, $options);
        $builder->add('ambassadeur', 'entity', array(
                'choices' => $this->formUserManager->getAmbassadeursChoices($this->utilisateurConnecte->getRegion()),
                'class' => 'HopitalNumerique\UserBundle\Entity\User',
                'property' => 'appellation',
                'label' => 'Ambassadeur',
                'required' => true,
                'read_only' => false
            ))
            //->remove('referent')
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
