<?php

namespace HopitalNumerique\InterventionBundle\Form\InterventionDemande\Edition;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use HopitalNumerique\InterventionBundle\Form\InterventionDemande\CmsiType as InterventionDemandeCmsiType;
use Symfony\Component\Security\Core\SecurityContext;
use HopitalNumerique\InterventionBundle\Manager\InterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\InterventionDemandeManager as FormInterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\UserManager as FormUserManager;
use HopitalNumerique\InterventionBundle\Manager\Form\EtablissementManager as FormEtablissementManager;
use Symfony\Component\Validator\Validator\LegacyValidator;

/**
 * Formulaire d'édition d'une demande d'intervention spécifique au CMSI.
 */
class CmsiType extends InterventionDemandeCmsiType
{
    /**
     * Constructeur du formulaire d'édition de demande d'intervention spécifique au CMSI.
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
            ->add('ambassadeur', EntityType::class, [
                'choices' => $this->formUserManager->getAmbassadeursChoices($this->utilisateurConnecte->getRegion()),
                'class' => 'HopitalNumerique\UserBundle\Entity\User',
                'choice_label' => 'appellation',
                'label' => 'Ambassadeur',
                'required' => true,
                'read_only' => false,
            ])
            ->remove('referent')
        ;
    }
}
