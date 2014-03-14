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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validator;

/**
 * Formulaire d'édition d'une demande d'intervention spécifique au CMSI.
 */
class CmsiType extends InterventionDemandeCmsiType
{
    /**
     * Constructeur du formulaire d'édition de demande d'intervention spécifique au CMSI.
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
        $builder
            ->add('ambassadeur', 'entity', array(
                'choices' => $this->container->get('hopitalnumerique_intervention.manager.form_user')->getAmbassadeursChoices($this->container->get('security.context')->getToken()->getUser()->getRegion()),
                'class' => 'HopitalNumerique\UserBundle\Entity\User',
                'property' => 'appellation',
                'label' => 'Ambassadeur',
                'required' => true
            ))
            ->remove('interventionType')
            ->remove('referent')
            ->remove('objets')
            ->remove('champLibre')
            ->remove('rdvInformations')
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
