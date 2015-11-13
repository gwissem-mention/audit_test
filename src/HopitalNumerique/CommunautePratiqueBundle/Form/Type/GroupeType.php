<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\UserBundle\Manager\UserManager;

/**
 * Formulaire d'édition d'un groupe de la communauté de pratique.
 */
class GroupeType extends \Symfony\Component\Form\AbstractType
{
    /**
     * @var \HopitalNumerique\UserBundle\Manager\UserManager UserManager
     */
    private $userManager;


    /**
     * Constructeur.
     */
    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }


    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('domaine', 'entity', array(
                'class' => 'HopitalNumeriqueDomaineBundle:Domaine',
                'disabled' => (null !== $builder->getData()->getId())
            ))
            ->add('titre', 'text', array(
                'required' => true,
                'attr' => array(
                    'class' => 'validate[required]',
                    'maxlength' => 255
                )
            ))
            ->add('descriptionCourte', 'textarea', array(
                'required' => true,
                'attr' => array('class' => 'validate[required]')
            ))
            ->add('descriptionHtml', 'textarea', array(
                'label' => 'Description',
                'required' => true,
                'attr' => array('class' => 'validate[required] tinyMceCode')
            ))
            ->add('nombreParticipantsMaximum', 'integer', array(
                'required' => true,
                'attr' => array(
                    'class' => 'validate[required,min[0]]'
                )
            ))
            ->add('dateInscriptionOuverture', 'genemu_jquerydate', array(
                'label' => 'Date d\'ouverture des inscriptions',
                'required' => true,
                'widget' => 'single_text',
                'attr' => array(
                    'class' => 'validate[required]'
                )
            ))
            ->add('dateDemarrage', 'genemu_jquerydate', array(
                'label' => 'Date de démarrage du groupe',
                'required' => true,
                'widget' => 'single_text',
                'attr' => array(
                    'class' => 'validate[required]'
                )
            ))
            ->add('dateFin', 'genemu_jquerydate', array(
                'label' => 'Date de fin du groupe',
                'required' => true,
                'widget' => 'single_text',
                'attr' => array(
                    'class' => 'validate[required]'
                )
            ))
            ->add('questionnaire', 'entity', array(
                'class' => 'HopitalNumeriqueQuestionnaireBundle:Questionnaire',
                'required' => true,
                'empty_value' => ' ',
                'attr' => array(
                    'class' => 'validate[required]'
                )
            ))
            ->add('animateurs', 'genemu_jqueryselect2_entity', array(
                'class' => 'HopitalNumeriqueUserBundle:User',
                'choices' => $this->userManager->findUsersByDomaine($builder->getData()->getDomaine()->getId()),
                'property' => 'appellation',
                'multiple' => true
            ))
            ->add('vedette', 'checkbox', array(
                'label' => 'En vedette',
                'required' => false,
                'attr' => array( 'class'=> 'checkbox' )
            ))
            ->add('actif', 'checkbox', array(
                'required' => false,
                'attr' => array( 'class'=> 'checkbox' )
            ))
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_communautepratiquebundle_groupe';
    }
}
