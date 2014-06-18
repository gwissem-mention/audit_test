<?php
/**
 * Formulaire d'une demande d'intervention spécifique à un établissement.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Form\InterventionDemande;

use Symfony\Component\Form\FormBuilderInterface;
use HopitalNumerique\InterventionBundle\Form\InterventionDemandeType;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Validator\Validator\LegacyValidator;
use HopitalNumerique\InterventionBundle\Manager\InterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\InterventionDemandeManager as FormInterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\UserManager as FormUserManager;
use HopitalNumerique\InterventionBundle\Manager\Form\EtablissementManager as FormEtablissementManager;

/**
 * Formulaire d'une demande d'intervention spécifique à un établissement.
 */
class EtablissementType extends InterventionDemandeType
{
    /**
     * Constructeur du formulaire de demande d'intervention à un établissement.
     *
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext SecurityContext de l'application
     * @param \Symfony\Component\Validator\Validator $validator Validator
     * @param \HopitalNumerique\InterventionBundle\Manager\InterventionDemandeManager $interventionDemandeManager Manager InterventionDemande
     * @param \HopitalNumerique\InterventionBundle\Manager\Form\InterventionDemandeManager $formInterventionDemandeManager Manager Form\InterventionDemande
     * @param \HopitalNumerique\InterventionBundle\Manager\Form\UserManager $formUserManager Manager Form\User
     * @param \HopitalNumerique\InterventionBundle\Manager\Form\EtablissementManager $formEtablissementManager Manager Form\Etablissement
     * @return void
     */
    public function __construct(SecurityContext $securityContext, $validator, InterventionDemandeManager $interventionDemandeManager, FormInterventionDemandeManager $formInterventionDemandeManager, FormUserManager $formUserManager, FormEtablissementManager $formEtablissementManager)
    {
        parent::__construct($securityContext, $validator, $interventionDemandeManager, $formInterventionDemandeManager, $formUserManager, $formEtablissementManager);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->remove('ambassadeur')
            ->remove('referent')
            ->remove('cmsiCommentaire');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_interventionbundle_interventiondemande_etablissement';
    }
}
