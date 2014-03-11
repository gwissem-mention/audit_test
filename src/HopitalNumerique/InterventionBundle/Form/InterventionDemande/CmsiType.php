<?php
/**
 * Formulaire d'une demande d'intervention spécifique au CMSI.
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
class CmsiType extends InterventionDemandeType
{
    /**
     * Constructeur du formulaire de demande d'intervention spécifique au CMSI.
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
                ->add('etablissements', 'entity',
                        array(
                                'choices' => $this->container->get('hopitalnumerique_intervention.manager.form_etablissement')
                                        ->getEtablissementsChoices(),
                                'class' => 'HopitalNumerique\EtablissementBundle\Entity\Etablissement', 'property' => 'nom',
                                'multiple' => true, 'label' => 'Rattacher des établissements à ma demande, parmi', 'required' => false,
                                'attr' => array('class' => 'hopitalnumerique_interventionbundle_interventiondemande_etablissements')))
                ->remove('description')->remove('difficulteDescription');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_interventionbundle_interventiondemande_cmsi';
    }
}
