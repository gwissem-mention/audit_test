<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\QuestionnaireBundle\Repository\QuestionnaireRepository;
use HopitalNumerique\UserBundle\Repository\UserRepository;
use Nodevo\RoleBundle\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
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
     * @var UserRepository $userRepository
     */
    private $userRepository;

    /**
     * @var QuestionnaireRepository $questionnaireRepository
     */
    protected $questionnaireRepository;

    /**
     * @var \HopitalNumerique\UserBundle\Entity\User Utilisateur connecté
     */
    private $user;

    /**
     * Constructeur.
     */
    public function __construct(SecurityContextInterface $securityContext, UserRepository $userRepository, QuestionnaireRepository $questionnaireRepository)
    {
        $this->userRepository = $userRepository;
        $this->questionnaireRepository = $questionnaireRepository;

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
        $hasDomaine = $builder->getData()->getDomains()->count() > 0;

        $builder
            ->add('domains', EntityType::class, [
                'class' => Domaine::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('domain')
                        ->andWhere('domain.id IN (:userDomains)')
                        ->setParameter('userDomains', $this->user->getDomaines())
                    ;
                },
                'multiple' => true,
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
                ->add('animateurs', 'genemu_jqueryselect2_entity', [
                    'class' => 'HopitalNumeriqueUserBundle:User',
                    'choices' => $this->userRepository->getCommunautePratiqueMembersInDomains($builder->getData()->getDomains()),
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
                ->add('requiredRoles', EntityType::class, [
                    'class' => Role::class,
                    'property' => 'name',
                    'multiple' => true,
                    'required' => false,
                ])
            ;
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $this->addSurveyField($event->getData()->getDomains(), $event->getForm());
        });

        $builder->get('domains')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $this->addSurveyField($event->getForm()->getData(), $event->getForm()->getParent());
        });
    }

    private function addSurveyField($domains, FormInterface $form)
    {
        $form->add('questionnaire', 'entity', [
            'class' => 'HopitalNumeriqueQuestionnaireBundle:Questionnaire',
            'choices' => $this->questionnaireRepository->findByDomains($domains),
            'required' => true,
            'empty_value' => ' ',
            'attr' => [
                'class' => 'validate[required]',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Groupe::class,
            'label_format' => 'admin.group.edit.form.%name%.label',
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
