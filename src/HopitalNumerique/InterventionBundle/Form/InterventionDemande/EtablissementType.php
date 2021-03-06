<?php

namespace HopitalNumerique\InterventionBundle\Form\InterventionDemande;

use Symfony\Component\Form\FormBuilderInterface;
use HopitalNumerique\InterventionBundle\Form\InterventionDemandeType;
use Symfony\Component\Security\Core\SecurityContext;
use HopitalNumerique\InterventionBundle\Manager\InterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\InterventionDemandeManager as FormInterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\UserManager as FormUserManager;
use HopitalNumerique\InterventionBundle\Manager\Form\EtablissementManager as FormEtablissementManager;
use Symfony\Component\Validator\Validator\LegacyValidator;

/**
 * Formulaire d'une demande d'intervention spécifique à un établissement.
 */
class EtablissementType extends InterventionDemandeType
{
    /**
     * Constructeur du formulaire de demande d'intervention à un établissement.
     *
     * @param SecurityContext                $securityContext                SecurityContext de l'application
     * @param LegacyValidator                $validator                      LegacyValidator
     * @param InterventionDemandeManager     $interventionDemandeManager     Manager InterventionDemande
     * @param FormInterventionDemandeManager $formInterventionDemandeManager Manager Form\InterventionDemande
     * @param FormUserManager                $formUserManager                Manager Form\User
     * @param FormEtablissementManager       $formEtablissementManager       Manager Form\Etablissement
     */
    public function __construct(
        SecurityContext $securityContext,
        $validator,
        InterventionDemandeManager $interventionDemandeManager,
        FormInterventionDemandeManager $formInterventionDemandeManager,
        FormUserManager $formUserManager,
        FormEtablissementManager $formEtablissementManager
    ) {
        parent::__construct(
            $securityContext,
            $validator,
            $interventionDemandeManager,
            $formInterventionDemandeManager,
            $formUserManager,
            $formEtablissementManager
        );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->remove('ambassadeur')
            ->remove('referent')
            ->remove('cmsiCommentaire');
    }
}
