<?php
/**
 * Formulaire d'édition d'une demande d'intervention spécifique au CMSI.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Form\InterventionDemande\Edition;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\InterventionBundle\Form\InterventionDemande\CmsiType as InterventionDemandeCmsiType;
use HopitalNumerique\InterventionBundle\Form\UserType;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Validator\Validator;
use HopitalNumerique\InterventionBundle\Manager\InterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\InterventionDemandeManager as FormInterventionDemandeManager;
use HopitalNumerique\InterventionBundle\Manager\Form\UserManager as FormUserManager;
use HopitalNumerique\InterventionBundle\Manager\Form\EtablissementManager as FormEtablissementManager;

/**
 * Formulaire d'édition d'une demande d'intervention spécifique au CMSI.
 */
class CmsiType extends InterventionDemandeCmsiType
{
    /**
     * Constructeur du formulaire d'édition de demande d'intervention spécifique au CMSI.
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
            ->add('ambassadeur', 'entity', array(
                'choices' => $this->formUserManager->getAmbassadeursChoices($this->utilisateurConnecte->getRegion()),
                'class' => 'HopitalNumerique\UserBundle\Entity\User',
                'property' => 'appellation',
                'label' => 'Ambassadeur',
                'required' => true,
                'read_only' => false
            ))
            ->remove('referent')
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_interventionbundle_interventiondemande_edition_cmsi';
    }
}
