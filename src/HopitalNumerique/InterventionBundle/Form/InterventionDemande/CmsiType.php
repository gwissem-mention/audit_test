<?php
/**
 * Formulaire d'une demande d'intervention.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Form\InterventionDemande;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\InterventionBundle\Form\InterventionDemandeType;
use HopitalNumerique\InterventionBundle\Form\UserType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Formulaire d'une demande d'intervention spécifique au CMSI.
 */
class InterventionDemandeType extends InterventionDemandeType
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     */
    private $container;
    
    /**
     * Constructeur du formulaire Utilisateur.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }
    
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        
        $builder
            //->add('referent', $this->container->get('hopitalnumerique_intervention.type.user'))
            ->add(
                'interventionType',
                'entity',
                array(
                    'choices' => $this->container->get('hopitalnumerique_intervention.manager.form_intervention_demande')->getInterventionTypesChoices(),
                    'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                    'property' => 'libelle',
                    'label' => 'Type d\'intervention souhaitée',
                    'required' => true
                )
            )
            ->add(
                'etablissements',
                'choice',
                array(
                    'multiple' => true,
                    'label' => 'Rattacher d\'autres établissements à ma demande, parmi',
                    'required' => false
                )
            )
            ->add(
                'autresEtablissements',
                'textarea',
                array(
                    'label' => 'Rattacher d\'autres établissements à ma demande',
                    'required' => false
                )
            )
            ->add(
                'objets',
                'entity',
                array(
                    'label' => 'Ma sollicitation porte sur la/les production(s) ANAP suivante(s)',
                    'class' => 'HopitalNumeriqueObjetBundle:Objet',
                    'property' => 'titre',
                    'multiple' => true
                )
            )
            ->add(
                'description',
                'textarea',
                array(
                    'label' => 'Description succinte de mon projet',
                    'required' => false
                )
            )
            ->add(
                'difficulteDescription',
                'textarea',
                array(
                    'label' => 'Description de ma difficulté',
                    'required' => false
                )
            )
            ->add(
                'champLibre',
                'textarea',
                array(
                    'label' => 'Champ libre',
                    'required' => false
                )
            )
            ->add(
                'rdvInformations',
                'textarea',
                array(
                    'label' => 'Informations pour la prise de rendez-vous (échéance, disponibilités, etc)',
                    'required' => false
                )
            )
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_interventionbundle_interventiondemande_cmsi';
    }
}
