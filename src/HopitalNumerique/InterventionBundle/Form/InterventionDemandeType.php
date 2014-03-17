<?php
/**
 * Formulaire d'une demande d'intervention.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\InterventionBundle\Form\UserType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Validator\Validator;

/**
 * Formulaire d'une demande d'intervention.
 */
class InterventionDemandeType extends AbstractType
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     */
    protected $container;
    /**
     * @var \HopitalNumerique\UserBundle\Entity\User Utilisateur connecté
     */
    protected $utilisateurConnecte;
    /**
     * @var array Pour la validation du formulaire
     */
    private $_constraints = array();

    /**
     * Constructeur du formulaire de demande d'intervention.
     * 
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     * @return void
     */
    public function __construct(ContainerInterface $container, Validator $validator)
    {
        $this->container = $container;
        $this->_constraints = $this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->getConstraints($validator);
        $this->utilisateurConnecte = $this->container->get('security.context')->getToken()->getUser();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $interventionDemande = $options['interventionDemande'];
        
        $builder
            //->add('referent', $this->container->get('hopitalnumerique_intervention.type.user'))
            ->add('interventionType', 'entity', array(
                'choices' => $this->container->get('hopitalnumerique_intervention.manager.form_intervention_demande')->getInterventionTypesChoices(),
                'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                'property' => 'libelle',
                'label' => 'Type d\'intervention souhaitée',
                'empty_value' => '-',
                'required' => true,
                'attr' => array('class' => $this->_constraints['interventionType']['class'] )
            ))
            ->add('region', 'entity', array(
                'label' => 'Région des établissements',
                'choices' => $this->container->get('hopitalnumerique_intervention.manager.form_user')->getRegionsChoices(),
                'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                'property' => 'libelle',
                'mapped' => false,
                'required' => true,
                'attr' => array('class' => 'hopitalnumerique_interventionbundle_interventiondemande_region')
            ))
            ->add('etablissements', 'entity', array(
                'choices' => $this->container->get('hopitalnumerique_intervention.manager.form_etablissement')->getEtablissementsChoices(),
                'class' => 'HopitalNumerique\EtablissementBundle\Entity\Etablissement',
                'property' => 'nom',
                'multiple' => true,
                'label' => 'Rattacher d\'autres établissements à ma demande, parmi',
                'required' => false,
                'attr' => array('class' => 'hopitalnumerique_interventionbundle_interventiondemande_etablissements')
            ))
            ->add('referent', 'entity', array(
                'choices' => $this->container->get('hopitalnumerique_intervention.manager.form_user')->getUsersChoices(),
                'class' => 'HopitalNumerique\UserBundle\Entity\User',
                'label' => 'Référent de la demande',
                'required' => true,
                'attr' => array('class' => 'hopitalnumerique_interventionbundle_interventiondemande_referent '.$this->_constraints['referent']['class'])
            ))
            ->add('autresEtablissements', 'textarea', array(
                'label' => 'Attacher d\'autres établissements à ma demande',
                'required' => false
            ))
            ->add('objets', 'entity', array(
                'choices' => $this->container->get('hopitalnumerique_intervention.manager.form_intervention_demande')->getObjetsChoices($interventionDemande->getAmbassadeur()),
                'label' => 'Ma sollicitation porte sur la/les production(s) ANAP suivante(s)',
                'class' => 'HopitalNumeriqueObjetBundle:Objet',
                'property' => 'titre',
                'multiple' => true
            ))
            ->add('description', 'textarea', array(
                'label' => 'Description succinte de mon projet',
                'required' => false
            ))
            ->add('difficulteDescription', 'textarea', array(
                'label' => 'Description de ma difficulté',
                'required' => false
            ))
            ->add('champLibre', 'textarea', array(
                'label' => 'Champ libre',
                'required' => false
            ))
            ->add('rdvInformations', 'textarea', array(
                'label' => 'Informations pour la prise de rendez-vous (échéance, disponibilités, etc)',
                'required' => false
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\InterventionBundle\Entity\InterventionDemande',
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
