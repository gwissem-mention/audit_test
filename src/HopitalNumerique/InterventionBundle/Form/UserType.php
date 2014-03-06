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
use Symfony\Component\Validator\Validator;

/**
 * Formulaire avec les champs propres aux utilisateurs.
 */
class UserType extends AbstractType
{
    /**
     * @var \Symfony\Component\Validator\Validator $validator Validator du formulaire
     */
    private $_constraints = array();
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
    public function __construct(ContainerInterface $container, Validator $validator)
    {
        $this->container = $container;
        $this->_constraints = $this->container->get('hopitalnumerique_user.manager.user')->getConstraints($validator);
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
                    'required' => true,
                    'attr' => array('class' => $this->_constraints['civilite']['class'] )
                )
            )
            ->add(
                'titre',
                'entity',
                array(
                    'choices' => $this->container->get('hopitalnumerique_intervention.manager.form_user')->getTitresChoices(),
                    'class' => 'HopitalNumerique\ReferenceBundle\Entity\Reference',
                    'property' => 'libelle',
                    'required' => true,
                    'attr' => array()
                )
            )
            ->add(
                'nom',
                'text',
                array(
                    'required' => true,
                    'max_length' => $this->_constraints['nom']['maxlength'],
                    'attr' => array('class' => $this->_constraints['nom']['class'])
                )
            )
            ->add(
                'prenom',
                'text',
                array(
                    'label' => 'Prénom',
                    'required' => true,
                    'max_length' => $this->_constraints['prenom']['maxlength'],
                    'attr' => array('class' => $this->_constraints['prenom']['class'])
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
                    'required' => true,
                    'max_length' => $this->_constraints['telephoneDirect']['maxlength'],
                    'attr' => array(
                        'class' => $this->_constraints['telephoneDirect']['class'],
                        'data-mask' => $this->_constraints['telephoneDirect']['mask']
                    )
                )
            )
            ->add(
                'telephonePortable',
                'text',
                array(
                    'label' => 'Téléphone portable',
                    'required' => false,
                    'max_length' => $this->_constraints['telephonePortable']['maxlength'],
                    'attr' => array(
                        'class' => $this->_constraints['telephonePortable']['class'],
                        'data-mask' => $this->_constraints['telephonePortable']['mask']
                    )
                )
            )
            ->add(
                'contactAutre',
                'textarea',
                array(
                    'required' => false,
                    'attr' => array()
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
                    'required' => true,
                    'attr' => array()
                )
            )
            ->add(
                'etablissementRattachementSante',
                'choice',
                array(
                    'label' => 'Établissement de santé de rattachement',
                    /*'class' => 'HopitalNumerique\EtablissementBundle\Entity\Etablissement',
                    'property' => 'nom',
                    'empty_value' => '',
                    'required' => false*/
                    'attr' => array()
                )
            )
            ->add(
                'autreStructureRattachementSante',
                'text',
                array(
                    'label' => 'Autre établissement de rattachement',
                    'max_length' => $this->_constraints['autreStructureRattachementSante']['maxlength'],
                    'attr' => array('class' => $this->_constraints['autreStructureRattachementSante']['class'])
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
                    'required' => true,
                    'attr' => array()
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
