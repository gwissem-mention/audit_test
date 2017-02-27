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
     * Constructeur.
     */
    public function __construct(SecurityContextInterface $securityContext, DocumentManager $documentManager)
    {
        $user = (null !== $securityContext->getToken() ? $securityContext->getToken()->getUser() : null);
        if (!($user instanceof User)) {
            throw new \Exception('Utilisateur non connecté');
        }

        $this->documentManager = $documentManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe
         */
        $groupe = $builder->getData()->getGroupe();

        $builder
            ->add('questionPosee', 'text', [
                'label' => 'Question posée',
                'required' => true,
                'attr' => [
                    'class' => 'validate[required]',
                    'maxlength' => 255,
                ],
            ])
            ->add('contexte', 'textarea', [
                'label' => 'Éléments de contexte à prendre en compte',
                'required' => true,
                'attr' => [
                    'class' => 'validate[required]',
                    'rows' => 4,
                ],
            ])
            ->add('description', 'textarea', [
                'label' => 'Description complète du problème',
                'required' => true,
                'attr' => [
                    'class' => 'validate[required]',
                    'rows' => 4,
                ],
            ])
            ->add('aideAttendue', 'textarea', [
                'label' => 'Aide attendue',
                'required' => true,
                'attr' => [
                    'class' => 'validate[required]',
                    'rows' => 4,
                ],
            ])
            ->add('resume', 'textarea', [
                'label' => 'En résumé...',
                'required' => true,
                'attr' => [
                    'class' => 'validate[required]',
                    'rows' => 4,
                ],
            ])
            ->add('documents', 'entity', [
                'class' => 'HopitalNumeriqueCommunautePratiqueBundle:Document',
                'choices' => $this->documentManager->findBy(['groupe' => $groupe, 'user' => $builder->getData()->getUser()]),
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_communautepratiquebundle_fiche';
    }
}
