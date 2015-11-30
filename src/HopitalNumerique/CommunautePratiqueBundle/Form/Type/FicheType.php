<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use HopitalNumerique\CommunautePratiqueBundle\Manager\DocumentManager;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Formulaire d'édition d'une fiche de la communauté de pratique.
 */
class FicheType extends \Symfony\Component\Form\AbstractType
{
    /**
     * @var \HopitalNumerique\CommunautePratiqueBundle\Manager\DocumentManager DocumentManager
     */
    private $documentManager;


    /**
     * @var \HopitalNumerique\UserBundle\Entity\User Utilisateur connecté
     */
    private $user;


    /**
     * Constructeur.
     */
    public function __construct(SecurityContextInterface $securityContext, DocumentManager $documentManager)
    {
        $this->user = (null !== $securityContext->getToken() ? $securityContext->getToken()->getUser() : null);
        if (!($this->user instanceof User)) {
            throw new \Exception('Utilisateur non connecté');
        }

        $this->documentManager = $documentManager;
    }


    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe
         */
        $groupe = $builder->getData()->getGroupe();

        $builder
            ->add('questionPosee', 'text', array(
                'label' => 'Question posée',
                'required' => true,
                'attr' => array(
                    'class' => 'validate[required]',
                    'maxlength' => 255
                )
            ))
            ->add('contexte', 'textarea', array(
                'label' => 'Éléments de contexte à prendre en compte',
                'required' => true,
                'attr' => array(
                    'class' => 'validate[required]',
                    'rows' => 4
                )
            ))
            ->add('description', 'textarea', array(
                'label' => 'Description complète du problème',
                'required' => true,
                'attr' => array(
                    'class' => 'validate[required]',
                    'rows' => 4
                )
            ))
            ->add('aideAttendue', 'textarea', array(
                'label' => 'Aide attendue',
                'required' => true,
                'attr' => array(
                    'class' => 'validate[required]',
                    'rows' => 4
                )
            ))
            ->add('resume', 'textarea', array(
                'label' => 'En résumé...',
                'required' => true,
                'attr' => array(
                    'class' => 'validate[required]',
                    'rows' => 4
                )
            ))
            ->add('documents', 'entity', array(
                'class' => 'HopitalNumeriqueCommunautePratiqueBundle:Document',
                'choices' => $this->documentManager->findBy(array('groupe' => $groupe, 'user' => $this->user)),
                'multiple' => true,
                'expanded' => true
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_communautepratiquebundle_fiche';
    }
}
