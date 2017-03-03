<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager;
use Symfony\Component\Security\Core\SecurityContextInterface;
use HopitalNumerique\UserBundle\Entity\User;

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
     * @var \HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager QuestionnaireManager
     */
    private $questionnaireManager;

    /**
     * @var \HopitalNumerique\UserBundle\Entity\User Utilisateur connecté
     */
    private $user;

    /**
     * Constructeur.
     */
    public function __construct(SecurityContextInterface $securityContext, UserManager $userManager, QuestionnaireManager $questionnaireManager)
    {
        $this->userManager = $userManager;
        $this->questionnaireManager = $questionnaireManager;

        $this->user = (null !== $securityContext->getToken() ? $securityContext->getToken()->getUser() : null);
        if (!($this->user instanceof User)) {
            throw new \Exception('Utilisateur non connecté');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isCreation = (null === $builder->getData()->getId());
        $hasDomaine = (null !== $builder->getData()->getDomaine());

        $builder
            ->add('domaine', 'entity', [
                'class' => 'HopitalNumeriqueDomaineBundle:Domaine',
                'choices' => $this->user->getDomaines(),
                'disabled' => (!$isCreation),
            ])
        ;
        if ($hasDomaine) {
            $builder
                ->add('titre', 'text', [
                    'required' => true,
                    'attr' => [
                        'class' => 'validate[required]',
                        'maxlength' => 255,
                    ],
                ])
                ->add('descriptionCourte', 'textarea', [
                    'required' => true,
                    'attr' => ['class' => 'validate[required]'],
                ])
                ->add('descriptionHtml', 'textarea', [
                    'label' => 'Description',
                    'required' => true,
                    'attr' => ['class' => 'validate[required] tinyMceCode'],
                ])
                ->add('nombreParticipantsMaximum', 'integer', [
                    'required' => true,
                    'attr' => [
                        'class' => 'validate[required,min[0]]',
                    ],
                ])
                ->add('dateInscriptionOuverture', 'genemu_jquerydate', [
                    'label' => 'Date d\'ouverture des inscriptions',
                    'required' => true,
                    'widget' => 'single_text',
                    'attr' => [
                        'class' => 'validate[required]',
                    ],
                ])
                ->add('dateDemarrage', 'genemu_jquerydate', [
                    'label' => 'Date de démarrage du groupe',
                    'required' => true,
                    'widget' => 'single_text',
                    'attr' => [
                        'class' => 'validate[required]',
                    ],
                ])
                ->add('dateFin', 'genemu_jquerydate', [
                    'label' => 'Date de fin du groupe',
                    'required' => true,
                    'widget' => 'single_text',
                    'attr' => [
                        'class' => 'validate[required]',
                    ],
                ])
                ->add('questionnaire', 'entity', [
                    'class' => 'HopitalNumeriqueQuestionnaireBundle:Questionnaire',
                    'choices' => $this->questionnaireManager->findByDomaine($builder->getData()->getDomaine()),
                    'required' => true,
                    'empty_value' => ' ',
                    'attr' => [
                        'class' => 'validate[required]',
                    ],
                ])
                ->add('animateurs', 'genemu_jqueryselect2_entity', [
                    'class' => 'HopitalNumeriqueUserBundle:User',
                    'choices' => $this->userManager->findCommunautePratiqueMembres($builder->getData()->getDomaine()),
                    'by_reference' => false,
                    'property' => 'prenomNom',
                    'multiple' => true,
                ])
                ->add('vedette', 'checkbox', [
                    'label' => 'En vedette',
                    'required' => false,
                    'attr' => ['class' => 'checkbox'],
                ])
                ->add('actif', 'checkbox', [
                    'required' => false,
                    'attr' => ['class' => 'checkbox'],
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hopitalnumerique_communautepratiquebundle_groupe';
    }
}
