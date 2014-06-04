<?php
/**
 * Formulaire de création d'une demande d'intervention dans l'administration.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Form\InterventionDemande;

use Symfony\Component\Form\FormBuilderInterface;
use HopitalNumerique\InterventionBundle\Form\InterventionDemandeType;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Validator\Validator;
use HopitalNumerique\InterventionBundle\Manager\InterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\InterventionDemandeManager as FormInterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\InterventionInitiateurManager as FormInterventionInitiateurManager;
use HopitalNumerique\InterventionBundle\Manager\Form\UserManager as FormUserManager;
use HopitalNumerique\InterventionBundle\Manager\Form\EtablissementManager as FormEtablissementManager;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;

/**
 * Formulaire de création d'une demande d'intervention spécifique dans l'administration.
 */
class AdminType extends InterventionDemandeType
{
    /**
     * @var \HopitalNumerique\ObjetBundle\Manager\ObjetManager Manager de Objet
     */
    private $objetManager;
    /**
     * @var \HopitalNumerique\InterventionBundle\Manager\Form\InterventionInitiateurManager Manager Form\InterventionInitiateurManager
     */
    private $formInterventionInitiateurManager;
    
    /**
     * Constructeur du formulaire de création de demande d'intervention spécifique dans l'administration.
     *
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext SecurityContext de l'application
     * @param \Symfony\Component\Validator\Validator $validator Validator
     * @param \HopitalNumerique\InterventionBundle\Manager\InterventionDemandeManager $interventionDemandeManager Manager InterventionDemande
     * @param \HopitalNumerique\ObjetBundle\Manager\ObjetManager $objetManager Manager Objet
     * @param \HopitalNumerique\InterventionBundle\Manager\Form\InterventionDemandeManager $formInterventionDemandeManager Manager Form\InterventionDemande
     * @param \HopitalNumerique\InterventionBundle\Manager\Form\InterventionInitiateurManager $formInterventionInitiateurManager Manager Form\InterventionInitiateur
     * @param \HopitalNumerique\InterventionBundle\Manager\Form\UserManager $formUserManager Manager Form\User
     * @param \HopitalNumerique\InterventionBundle\Manager\Form\EtablissementManager $formEtablissementManager Manager Form\Etablissement
     * @return void
     */
    public function __construct(SecurityContext $securityContext, Validator $validator, InterventionDemandeManager $interventionDemandeManager, ObjetManager $objetManager, FormInterventionDemandeManager $formInterventionDemandeManager, FormInterventionInitiateurManager $formInterventionInitiateurManager, FormUserManager $formUserManager, FormEtablissementManager $formEtablissementManager)
    {
        parent::__construct($securityContext, $validator, $interventionDemandeManager, $formInterventionDemandeManager, $formUserManager, $formEtablissementManager);
        $this->objetManager = $objetManager;
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
                'data' => $this->interventionDemande->getInterventionInitiateur(),
                'property' => 'type',
                'label' => 'Initiateur de la demande',
                'required' => false,
                'read_only' => true,
                'disabled' => true
            ))
            ->add('interventionEtat', 'entity', array(
                'choices' => $this->formInterventionDemandeManager->getInterventionEtatsChoices(),
                'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                'property' => 'libelle',
                'label' => 'État actuel',
                'required' => true,
                'read_only' => true,
                'disabled' => true
            ))
            ->add('interventionType', 'entity', array(
                'choices'     => $this->formInterventionDemandeManager->getInterventionTypesChoices(),
                'class'       => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                'property'    => 'libelle',
                'label'       => 'Type d\'intervention souhaitée',
                'empty_value' => '-',
                'required'    => true,
                'attr'        => array('class' => $this->_constraints['interventionType']['class'] )
            ))
            ->add('region', 'entity', array(
                'label'    => 'Région des établissements',
                'choices'  => $this->formUserManager->getRegionsChoices(),
                'class'    => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                'property' => 'libelle',
                'mapped'   => false,
                'required' => true,
                'attr'     => array('class' => 'hopitalnumerique_interventionbundle_interventiondemande_region'),
                'data'     => $this->utilisateurConnecte->getRegion()
            ))
        ;
        parent::buildForm($builder, $options);
        $builder
            ->add('etablissements', 'entity', array(
                'choices' => $this->formEtablissementManager->getEtablissementsChoices(),
                'class' => 'HopitalNumerique\EtablissementBundle\Entity\Etablissement',
                'property' => 'nom',
                'multiple' => true,
                'label' => 'Rattacher des établissements à ma demande, parmi',
                'required' => true,
                'attr' => array('class' => 'hopitalnumerique_interventionbundle_interventiondemande_etablissements')
            ))
            ->add('ambassadeur', 'entity', array(
                'choices'   => $this->formUserManager->getAmbassadeursChoices(),
                'class'     => 'HopitalNumerique\UserBundle\Entity\User',
                'empty_value' => '-',
                'property'  => 'appellation',
                'label'     => 'Ambassadeur',
                'required'  => true,
                'read_only' => false,
                'attr' => array('class' => 'hopitalnumerique_interventionbundle_interventiondemande_ambassadeur')
            ))
            ->add('objets', 'entity', array(
                'choices'  => $this->objetManager->findAll(),
                'label'    => 'Ma sollicitation porte sur la/les production(s) ANAP suivante(s)',
                'class'    => 'HopitalNumeriqueObjetBundle:Objet',
                'property' => 'titre',
                'multiple' => true,
                'required' => false,
                'attr'     => array('class' => 'hopitalnumerique_interventionbundle_interventiondemande_objets')
            ))
            ->add('cmsiCommentaire', 'textarea', array(
                'label' => 'Commentaire CMSI',
                'required' => false,
                'read_only' => false
            ))
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_interventionbundle_interventiondemande_admin';
    }
}
