<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\UserBundle\Manager\UserManager;

/**
 * Formulaire d'ajout d'un membre à un groupe de la communauté de pratique.
 */
class AjoutType extends AbstractType
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
        /**
         * @var \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe
         */
        $groupe = $options['groupe'];

        $builder
            ->add('user', 'entity', array(
                'class' => 'HopitalNumeriqueUserBundle:User',
                'choices' => $this->userManager->findCommunautePratiqueMembresNotInGroupe($groupe),
                'property' => 'prenomNom',
                'attr' => array(
                    'class' => 'select2'
                )
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'validation_groups' => array('form_validation_only')
            ))
            ->setRequired(array('groupe'))
            ->setAllowedTypes(array('groupe' => 'HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe'))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_communautepratiquebundle_user_ajout';
    }
}
