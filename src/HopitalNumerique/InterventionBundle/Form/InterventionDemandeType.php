<?php

namespace HopitalNumerique\InterventionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\InterventionBundle\Form\UserType;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
            ->add('referent', new UserType($this->container))
            ->add('autresEtablissements')
            ->add('description')
            ->add('difficulteDescription')
            ->add('champLibre')
            ->add('rdvInformations')
            ->add('refusMessage')
            ->add('ambassadeur')
            ->add('cmsi')
            ->add('directeur')
            ->add('interventionInitiateur')
            ->add('interventionType')
            ->add('interventionEtat')
            ->add('evaluationEtat')
            ->add('remboursementEtat')
            ->add('ambassadeurs')
            ->add(
                'etablissements',
                'entity',
                array(
                    'class' => 'HopitalNumeriqueEtablissementBundle:Etablissement',
                    'property' => 'nom'
                )
            )
            ->add(
                'objets',
                'entity',
                array(
                    'class' => 'HopitalNumeriqueObjetBundle:Objet',
                    'property' => 'titre'
                )
            )
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
