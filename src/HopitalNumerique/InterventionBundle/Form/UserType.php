<?php
/**
 * Formulaire avec les champs propres aux utilisateurs.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Formulaire avec les champs propres aux utilisateurs.
 */
class UserType extends AbstractType
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
            ->add(
                'civilite',
                'entity',
                array(
                    'label' => 'Civilité',
                    'choices' => $this->container->get('hopitalnumerique_intervention.manager.form_user')->getCivilitesChoices(),
                    'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                    'property' => 'libelle',
                    'required' => true
                )
            )
            ->add(
                'titre',
                'entity',
                array(
                    'choices' => $this->container->get('hopitalnumerique_intervention.manager.form_user')->getTitresChoices(),
                    'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                    'property' => 'libelle',
                    'required' => true
                )
            )
            ->add(
                'nom',
                'text',
                array(
                    'required' => true
                )
            )
            ->add(
                'prenom',
                'text',
                array(
                    'label' => 'Prénom',
                    'required' => true
                )
            )
            ->add(
                'email',
                'email',
                array(
                    'label' => 'Adresse e-mail',
                    'read_only' => true
                )
            )
            ->add(
                'telephoneDirect',
                'text',
                array(
                    'label' => 'Téléphone direct',
                    'required' => true
                )
            )
            ->add(
                'telephonePortable',
                'text',
                array(
                    'label' => 'Téléphone portable',
                    'required' => false
                )
            )
            ->add(
                'contactAutre',
                'textarea',
                array(
                    'required' => false
                )
            )
            ->add(
                'region',
                'entity',
                array(
                    'label' => 'Région',
                    'choices' => $this->container->get('hopitalnumerique_intervention.manager.form_user')->getRegionsChoices(),
                    'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                    'property' => 'libelle',
                    'required' => true
                )
            )
            ->add(
                'departement',
                'choice',
                array(
                    'label' => 'Département',
                    'choices' => array(),
                    'required' => true
                )
            )
            ->add(
                'etablissementRattachementSante',
                'entity',
                array(
                    'label' => 'Établissement de santé de rattachement',
                    'class' => 'HopitalNumerique\EtablissementBundle\Entity\Etablissement',
                    'property' => 'nom',
                    'empty_value' => '',
                    'required' => false
                )
            )
            ->add(
                'autreStructureRattachementSante',
                'text',
                array(
                    'label' => 'Autre établissement de rattachement'
                )
            )
            ->add(
                'fonctionEtablissementSante',
                'entity',
                array(
                    'label' => 'Fonction dans l\'établissement de santé',
                    'choices' => $this->container->get('hopitalnumerique_intervention.manager.form_user')->getFonctionsEtablissementSanteChoices(),
                    'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                    'property' => 'libelle',
                    'empty_value' => '',
                    'required' => true
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
            'data_class' => 'HopitalNumerique\UserBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_interventionbundle_user';
    }
}
