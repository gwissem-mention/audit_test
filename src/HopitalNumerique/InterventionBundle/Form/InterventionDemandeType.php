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

/**
 * Formulaire d'une demande d'intervention.
 */
class InterventionDemandeType extends AbstractType
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
        $this->container = $container;
    }
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
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
                    'label' => 'Rattacher d\'autres établissements à ma demande, parmi'
                )
            )
            ->add(
                'autresEtablissements',
                'textarea',
                array(
                    'label' => 'Rattacher d\'autres établissements à ma demande'
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
                'description'
            )
            ->add('difficulteDescription')
            ->add('champLibre')
            ->add('rdvInformations')
            ->add('refusMessage')
            ->add('ambassadeur')
            ->add('cmsi')
            ->add('directeur')
            ->add('interventionInitiateur')
            ->add('interventionEtat')
            ->add('evaluationEtat')
            ->add('remboursementEtat')
            ->add('ambassadeurs')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\InterventionBundle\Entity\InterventionDemande'
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
