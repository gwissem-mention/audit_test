<?php

namespace HopitalNumerique\UserBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\EtablissementBundle\Manager\EtablissementManager;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Form\Type\HobbyType;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class UserAccountType
 */
class UserAccountType extends AbstractType
{
    /**
     * @var ReferenceManager
     */
    private $referenceManager;

    /**
     * @var EtablissementManager
     */
    private $organizationManager;

    /**
     * @var User $user
     */
    private $user;

    /**
     * UserAccountType constructor.
     *
     * @param ReferenceManager     $referenceManager
     * @param EtablissementManager $etablissementManager
     * @param TokenStorage         $tokenStorage
     */
    public function __construct(
        ReferenceManager $referenceManager,
        EtablissementManager $etablissementManager,
        TokenStorage $tokenStorage
    ) {
        $this->referenceManager = $referenceManager;
        $this->organizationManager = $etablissementManager;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastname', TextType::class)
            ->add('firstname', TextType::class)
            ->add('email', EmailType::class)
            ->add('pseudonym', TextType::class, [
                'required' => false,
            ])
            ->add('phoneNumber', TextType::class, [
                'required' => false,
            ])
            ->add('cellPhoneNumber', TextType::class, [
                'required' => false,
            ])
            ->add('otherContact', TextType::class, [
                'required' => false,
            ])
            ->add('profileType', EntityType::class, [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('CONTEXTE_METIER_INTERNAUTE'),
                'property' => 'libelle',
                'required' => false,
                'empty_value' => '-',
            ])
            ->add('jobType', EntityType::class, [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('CONTEXTE_FONCTION_INTERNAUTE'),
                'property' => 'libelle',
                'required' => false,
                'empty_value' => '-',
            ])
            ->add('jobLabel', TextType::class, [
                'required' => false,
            ])
            ->add('file', FileType::class, [
                'required' => false,
            ])
            ->add('path', HiddenType::class)
            ->add('region', EntityType::class, [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('REGION'),
                'property' => 'libelle',
                'required' => false,
                'empty_value' => '-',
            ])
            ->add('county', EntityType::class, [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('DEPARTEMENT'),
                'property' => 'libelle',
                'required' => false,
                'empty_value' => '-',
            ])
            ->add('organizationType', EntityType::class, [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('CONTEXTE_TYPE_ES'),
                'property' => 'libelle',
                'required' => false,
                'empty_value' => '-',
            ])
            ->add('organizationLabel', TextType::class, [
                'required' => false,
            ])
            ->add('activities', EntityType::class, [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('CONTEXTE_SPECIALITE_ES'),
                'property' => 'libelle',
                'required' => false,
                'expanded' => false,
                'multiple' => true,
            ])
            ->add('computerSkills', EntityType::class, [
                'class' => Reference::class,
                'choices' => $this->referenceManager->findByCode('LOGICIELS', true, true),
                'property' => 'libelle',
                'required' => false,
                'expanded' => false,
                'multiple' => true,
            ])
            ->add('presentation', TextareaType::class, [
                'required' => false,
            ])
            ->add('hobbies', CollectionType::class, [
                'type' => HobbyType::class,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ])
        ;

        $currentOrganization = $this->user->getOrganization();

        $organizationFormModifier = function (FormInterface $form, $full = false) use ($currentOrganization) {
            $fieldOptions = [
                'class'       => 'HopitalNumeriqueEtablissementBundle:Etablissement',
                'property'    => 'appellation',
                'required'    => false,
                'empty_value' => '-',
            ];

            if ($full) {
                $fieldOptions = array_merge(
                    $fieldOptions,
                    [
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('eta')->orderBy('eta.nom', 'ASC');
                        },
                    ]
                );
            } else {
                $fieldOptions['choices'] = is_null($currentOrganization)
                    ? []
                    : [$currentOrganization]
                ;
            }

            $form->add('organization', EntityType::class, $fieldOptions);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($organizationFormModifier) {
                $organizationFormModifier($event->getForm());
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($organizationFormModifier) {
                $organizationFormModifier($event->getForm(), true);
            }
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'label_format' => 'account.user.%name%',
        ]);
    }
}
