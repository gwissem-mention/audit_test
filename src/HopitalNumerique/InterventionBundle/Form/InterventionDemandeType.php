<?php
/**
 * Formulaire d'une demande d'intervention.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Form;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Validator;
use HopitalNumerique\InterventionBundle\Manager\InterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\InterventionDemandeManager as FormInterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\UserManager as FormUserManager;
use HopitalNumerique\InterventionBundle\Manager\Form\EtablissementManager as FormEtablissementManager;

/**
 * Formulaire d'une demande d'intervention.
 */
abstract class InterventionDemandeType extends AbstractType
{
    /**
     * @var array Pour la validation du formulaire
     */
    protected $_constraints = array();
    /**
     * @var \HopitalNumerique\InterventionBundle\Manager\Form\InterventionDemandeManager Manager Form\InterventionDemande
     */
    protected $formInterventionDemandeManager;
    /**
     * @var \HopitalNumerique\InterventionBundle\Manager\Form\UserManager Manager Form\User
     */
    protected $formUserManager;
    /**
     * @var \HopitalNumerique\InterventionBundle\Manager\Form\EtablissementManager Manager Form\Etablissement
     */
    protected $formEtablissementManager;

    /**
     * @var \HopitalNumerique\UserBundle\Entity\User Utilisateur connecté
     */
    protected $utilisateurConnecte;
    /**
     * @var \HopitalNumerique\InterventionBundle\Manager\InterventionDemande La demande d'intervention ouverte
     */
    protected $interventionDemande;

    /**
     * Constructeur du formulaire de demande d'intervention.
     * 
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext SecurityContext de l'application
     * @param \Symfony\Component\Validator\Validator $validator Validator
     * @param \Nodevo\InterventionBundle\Manager\InterventionDemandeManager $interventionDemandeManager Manager InterventionDemande
     * @param \Nodevo\InterventionBundle\Manager\Form\InterventionDemandeManager $formInterventionDemandeManager Manager Form\InterventionDemande
     * @param \Nodevo\InterventionBundle\Manager\Form\UserManager $formUserManager Manager Form\User
     * @param \Nodevo\InterventionBundle\Manager\Form\EtablissementManager $formEtablissementManager Manager Form\Etablissement
     * @return void
     */
    public function __construct(SecurityContext $securityContext, Validator $validator, InterventionDemandeManager $interventionDemandeManager, FormInterventionDemandeManager $formInterventionDemandeManager, FormUserManager $formUserManager, FormEtablissementManager $formEtablissementManager)
    {
        $this->_constraints                   = $interventionDemandeManager->getConstraints($validator);
        $this->formInterventionDemandeManager = $formInterventionDemandeManager;
        $this->formUserManager                = $formUserManager;
        $this->formEtablissementManager       = $formEtablissementManager;
        
        $this->utilisateurConnecte = $securityContext->getToken()->getUser();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->interventionDemande = $options['interventionDemande'];
        
        $builder
            ->add('ambassadeur', 'entity', array(
                'choices'   => $this->formUserManager->getAmbassadeursChoices($this->utilisateurConnecte->getRegion()),
                'class'     => 'HopitalNumerique\UserBundle\Entity\User',
                'property'  => 'appellation',
                'label'     => 'Ambassadeur',
                'required'  => true,
                'read_only' => true
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
            ->add('etablissements', 'entity', array(
                'choices'  => $this->formEtablissementManager->getEtablissementsChoices(),
                'class'    => 'HopitalNumerique\EtablissementBundle\Entity\Etablissement',
                'property' => 'nom',
                'multiple' => true,
                'label'    => 'Rattacher d\'autres établissements à ma demande, parmi',
                'required' => false,
                'attr'     => array('class' => 'hopitalnumerique_interventionbundle_interventiondemande_etablissements')
            ))
            ->add('referent', 'entity', array(
                'choices'  => $this->formUserManager->getReferentsChoices(),
                'class'    => 'HopitalNumerique\UserBundle\Entity\User',
                'label'    => 'Demandeur',
                'required' => true,
                'attr'     => array('class' => 'hopitalnumerique_interventionbundle_interventiondemande_referent '.$this->_constraints['referent']['class'])
            ))
            ->add('autresEtablissements', 'textarea', array(
                'label'    => 'Attacher d\'autres établissements à ma demande',
                'required' => false
            ))
            ->add('objets', 'entity', array(
                'choices'  => $this->formInterventionDemandeManager->getObjetsChoices($this->interventionDemande->getAmbassadeur()),
                'label'    => 'Ma sollicitation porte sur la/les production(s) ANAP suivante(s)',
                'class'    => 'HopitalNumeriqueObjetBundle:Objet',
                'property' => 'titre',
                'multiple' => true,
                'required' => false,
                'attr'     => array('class' => 'hopitalnumerique_interventionbundle_interventiondemande_objets')
            ))
            ->add('description', 'textarea', array(
                'label'    => 'Description succincte de mon projet',
                'required' => false
            ))
            ->add('difficulteDescription', 'textarea', array(
                'label'    => 'Description de ma difficulté',
                'required' => false
            ))
            ->add('champLibre', 'textarea', array(
                'label'    => 'Champ libre',
                'required' => false
            ))
            ->add('rdvInformations', 'textarea', array(
                'label'    => 'Informations pour la prise de rendez-vous (échéance, disponibilités, etc)',
                'required' => false
            ))
            ->add('cmsiCommentaire', 'textarea', array(
                'label'     => 'Commentaire CMSI',
                'required'  => false,
                'read_only' => true
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'          => 'HopitalNumerique\InterventionBundle\Entity\InterventionDemande',
            'interventionDemande' => null
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_interventionbundle_interventiondemande';
    }
}
