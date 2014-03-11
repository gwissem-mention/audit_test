<?php
/**
 * Formulaire avec les champs propres aux utilisateurs pour un CMSI.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Form\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\InterventionBundle\Form\UserType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validator;

/**
 * Formulaire avec les champs propres aux utilisateurs pour un CMSI.
 */
class CmsiType extends UserType
{
    /**
     * Constructeur du formulaire Utilisateur pour un CMSI.
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
        $builder->remove('etablissementRattachementSante')->remove('autreStructureRattachementSante')->remove('fonctionEtablissementSante');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_interventionbundle_user_cmsi';
    }
}
