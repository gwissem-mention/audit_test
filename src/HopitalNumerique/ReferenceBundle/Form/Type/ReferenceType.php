<?php

namespace HopitalNumerique\ReferenceBundle\Form\Type;

use HopitalNumerique\UserBundle\Manager\UserManager;
use Nodevo\ToolsBundle\Tools\Systeme;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReferenceType extends AbstractType
{
    private $constraints = [];
    private $userManager;
    /**
     * @var ReferenceManager
     */
    private $referenceManager;

    public function __construct(
        ReferenceManager $manager,
        ValidatorInterface $validator,
        UserManager $userManager,
        ReferenceManager $referenceManager
    ) {
        $this->constraints = $manager->getConstraints($validator);
        $this->userManager = $userManager;
        $this->referenceManager = $referenceManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $connectedUser = $this->userManager->getUserConnected();

        if ($connectedUser->hasRoleAdmin()) {
            $builder
                ->add('domaines', EntityType::class, [
                    'class' => 'HopitalNumeriqueDomaineBundle:Domaine',
                    'property' => 'nom',
                    'required' => false,
                    'multiple' => true,
                    'label' => 'Domaine(s) associé(s)',
                    'empty_value' => ' - ',
                    'query_builder' => function (EntityRepository $er) use ($connectedUser) {
                        return $er->getDomainesUserConnectedForForm($connectedUser->getId());
                    },
                    'attr' => [
                        'class' => 'select2',
                    ],
                ])
            ;

            if ($connectedUser->hasRoleAdmin()) {
                $builder
                    ->add('allDomaines', CheckboxType::class, [
                        'label' => 'Tous les domaines',
                        'required' => false,
                    ])
                ;
            }

            $this->buildFormPartConcept($builder, $options);
            $this->buildFormPartListe($builder, $options);
            $this->buildFormPartReference($builder, $options);
        }
        $this->buildFormPartGlossaire($builder, $options);
    }

    /**
     * Construit la partie Concept du formulaire.
     *
     * @param FormBuilderInterface $builder Builder
     * @param array                $options Options
     */
    private function buildFormPartConcept(FormBuilderInterface $builder, array $options)
    {
        $referenceId = $options['data']->getId();

        $builder
            ->add('libelle', TextType::class, [
                'required' => true,
                'label' => 'Libellé du concept',
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'validate[required]',
                    'data-prompt-position' => 'bottomLeft',
                ],
            ])
            ->add('image', HiddenType::class, [
                'required' => false,
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image',
                'required' => false,
            ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $this->verifyImage($event->getForm(), $event->getData());
            })
            ->add('synonymes', CollectionType::class, [
                'label' => 'Synonymes',
                'type' => SynonymeType::class,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('champLexicalNoms', CollectionType::class, [
                'label' => 'Champ lexical',
                'type' => ChampLexicalNomType::class,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('parents', EntityType::class, [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'multiple' => true,
                'required' => false,
                'label' => 'Parents',
                'query_builder' => function (EntityRepository $er) use ($referenceId) {
                    $qb = $er->createQueryBuilder('ref')
                        ->andWhere('ref.lock = 0')
                        ->leftJoin('ref.parents', 'parent')
                        ->leftJoin('ref.codes', 'codes')
                        ->orderBy('parent.id, codes.label, ref.order', 'ASC');

                    if ($referenceId) {
                        $qb->andWhere("ref.id != $referenceId");
                    }

                    return $qb;
                },
            ])
            ->add('etat', EntityType::class, [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'choices' => $this->referenceManager->findByCode('ETAT'),
                'property' => 'libelle',
                'required' => true,
                'label' => 'Etat',
                'attr' => ['class' => $this->constraints['etat']['class']],
            ])
        ;
    }

    /**
     * Construit la partie Liste du formulaire.
     *
     * @param FormBuilderInterface $builder Builder
     * @param array                $options Options
     */
    private function buildFormPartListe(FormBuilderInterface $builder, array $options)
    {
        $attrCode = [
            'maxlength' => 255,
        ];
        if ($options['data']->getLock()) {
            $attrCode['readonly'] = 'readonly';
        }

        $builder
            ->add('codes', CollectionType::class, [
                'entry_type' => ReferenceCodeType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('order', NumberType::class, [
                'required' => true,
                'label' => 'Ordre d\'affichage',
                'attr' => ['class' => 'validate[required, custom[numberVirgule]]'],
            ])
        ;
    }

    /**
     * Construit la partie Référence du formulaire.
     *
     * @param FormBuilderInterface $builder Builder
     * @param array                $options Options
     */
    private function buildFormPartReference(FormBuilderInterface $builder, array $options)
    {
        $connectedUser = $this->userManager->getUserConnected();

        $builder
            ->add('reference', CheckboxType::class, [
                'required' => false,
                'label' => 'Est une référence ?',
            ])
            ->add('inRecherche', CheckboxType::class, [
                'required' => false,
                'label' => 'Présente dans la recherche ?',
            ])
            ->add('referenceLibelle', TextType::class, [
                'required' => false,
                'label' => 'Libellé de la référence (si différent du libellé du concept)',
                'attr' => [
                    'maxlength' => 255,
                ],
            ])
            ->add('domainesDisplay', EntityType::class, [
                'class' => 'HopitalNumeriqueDomaineBundle:Domaine',
                'property' => 'nom',
                'required' => false,
                'label' => 'Afficher un lien pour ces domaines :',
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) use ($connectedUser) {
                    return $er->getDomainesUserConnectedForForm($connectedUser->getId());
                },
                'attr' => [
                    'class' => 'select2',
                ],
            ])
        ;
    }

    /**
     * Construit la partie Glossaire du formulaire.
     *
     * @param FormBuilderInterface $builder Builder
     * @param array                $options Options
     */
    private function buildFormPartGlossaire(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('inGlossaire', CheckboxType::class, [
                'required' => false,
                'label' => 'Présent dans le glossaire ?',
            ])
            ->add('sigle', TextType::class, [
                'required' => false,
                'label' => 'Sigle',
                'attr' => [
                    'maxlength' => 255,
                ],
            ])
            ->add('glossaireLibelle', TextType::class, [
                'required' => false,
                'label' => 'Libellé dans le glossaire (si différent du libellé du concept)',
                'attr' => [
                    'maxlength' => 255,
                ],
            ])
            ->add('descriptionCourte', TextareaType::class, [
                'required' => false,
                'label' => 'Description courte <span title="Ce champ est requis" style="color:red;font-size:10px">*</span>',
                'attr' => [
                    'data-prompt-position' => 'bottomLeft',
                ],
            ])
            ->add('descriptionLongue', TextareaType::class, [
                'required' => false,
                'label' => 'Description longue',
                'attr' => [
                    'class' => 'tinyMce',
                ],
            ])
            ->add('casseSensible', CheckboxType::class, [
                'required' => false,
                'label' => 'Sensible à la casse ?',
            ])
        ;
    }

    /**
     * Vérifie la validité de l'image.
     *
     * @param FormInterface $form      Formulaire
     * @param Reference     $reference Référence
     */
    private function verifyImage(FormInterface $form, Reference $reference)
    {
        if (null !== $reference->getImageFile() && !$reference->imageFileIsValid()) {
            $form->get('imageFile')->addError(
                new FormError(
                    'Veuillez choisir une image inférieure à '
                    . intval(Systeme::getFileUploadMaxSize() / 1024 / 1024)
                    . ' Mo.'
                )
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reference::class,
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_reference_reference';
    }
}
