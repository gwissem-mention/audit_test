<?php
/**
 * Formulaire d'une demande d'intervention spécifique à un établissement.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Form\InterventionDemande;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\InterventionBundle\Form\InterventionDemandeType;
use HopitalNumerique\InterventionBundle\Form\UserType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validator;

/**
 * Formulaire d'une demande d'intervention spécifique à un établissement.
 */
class EtablissementType extends InterventionDemandeType
{
    /**
     * Constructeur du formulaire de demande d'intervention à un établissement.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     * @return void
     */
    public function __construct(ContainerInterface $container, Validator $validator)
    {
        parent::__construct($container, $validator);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('referent');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_interventionbundle_interventiondemande_etablissement';
    }
}
