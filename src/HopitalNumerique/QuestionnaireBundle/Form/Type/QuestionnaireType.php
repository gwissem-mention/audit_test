<?php

namespace HopitalNumerique\QuestionnaireBundle\Form\Type;

use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\QuestionnaireBundle\Entity\Question;
use HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager;
use HopitalNumerique\QuestionnaireBundle\Manager\ReponseManager;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Nodevo\ToolsBundle\Form\Type\NodevoCommentaireType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use HopitalNumerique\QuestionnaireBundle\Manager\OccurrenceManager;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;
use HopitalNumerique\QuestionnaireBundle\Entity\Occurrence;
use HopitalNumerique\UserBundle\Entity\User;
use Doctrine\ORM\Query\Expr;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class QuestionnaireType
 */
class QuestionnaireType extends AbstractType
{
    /**
     * @var OccurrenceType Formulaire OccurrenceType
     */
    private $occurrenceForm;

    private $readOnly = false;
    private $managerReponse;

    /**
     * @var QuestionnaireManager QuestionnaireManager
     */
    private $managerQuestionnaire;

    /**
     * @var OccurrenceManager OccurrenceManager
     */
    private $occurrenceManager;

    /**
     * @var UserManager UserManager
     */
    private $userManager;

    /**
     * @var Router
     */
    private $router;

    /**
     * QuestionnaireType constructor.
     *
     * @param OccurrenceType       $occurrenceForm
     * @param ReponseManager       $managerReponse
     * @param QuestionnaireManager $managerQuestionnaire
     * @param OccurrenceManager    $occurrenceManager
     * @param UserManager          $userManager
     * @param Router               $router
     */
    public function __construct(
        OccurrenceType $occurrenceForm,
        $managerReponse,
        $managerQuestionnaire,
        OccurrenceManager $occurrenceManager,
        UserManager $userManager,
        Router $router
    ) {
        $this->occurrenceForm       = $occurrenceForm;
        $this->managerReponse       = $managerReponse;
        $this->managerQuestionnaire = $managerQuestionnaire;
        $this->occurrenceManager    = $occurrenceManager;
        $this->userManager          = $userManager;
        $this->router               = $router;
    }

    /**
     * Ajout des éléments dans le formulaire, spécifie les labels, les widgets utilisés ainsi que l'obligation.
     *
     * @param FormBuilderInterface $builder Le builder contient les champs du formulaire
     * @param array                $options Data passée au formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $idUser = (isset($options['label_attr']['idUser'])
                   && !is_null($options['label_attr']['idUser'])
        ) ? $options['label_attr']['idUser'] : 0;

        $idQuestionnaire = (isset($options['label_attr']['idQuestionnaire'])
                            && !is_null($options['label_attr']['idQuestionnaire'])
        ) ? $options['label_attr']['idQuestionnaire'] : 0;

        $occurrence = (isset($options['label_attr']['occurrence']) ? $options['label_attr']['occurrence'] : null);

        /** @var Questionnaire $questionnaire */
        $questionnaire = $this->managerQuestionnaire->findOneBy(['id' => $idQuestionnaire]);

        /** @var User $user */
        $user = $this->userManager->findOneById($idUser);

        /*
         * Tableau de la route de redirection sous la forme :
         * array(
         *   'sauvegarde' => array( 'route' => nom_de_ma_route, 'arguments' => array ('keyArgument' => valueArgument))
         *   'quit'       => array( 'route' => nom_de_ma_route, 'arguments' => array ('keyArgument' => valueArgument))
         *  )
         **/
        $routeRedirection = (isset($options['label_attr']['routeRedirection'])
                             && !is_null($options['label_attr']['routeRedirection'])
        ) ? $options['label_attr']['routeRedirection'] : [];

        $this->readOnly = (isset($options['label_attr']['readOnly'])
                           && !is_null($options['label_attr']['readOnly'])
        ) ? $options['label_attr']['readOnly'] : false;

        //Si le showAllQuestions n'est pas reinseigné, par défaut on les affiches toutes
        if ((!isset($options['label_attr']['showAllQuestions'])
             || is_null($options['label_attr']['showAllQuestions']))
        ) {
            $options['label_attr']['showAllQuestions'] = true;
        }

        //Ajout d'un champ hidden pour récupérer les routes de redirection dans le controleur à la validation
        $builder->add(
            'routeRedirect',
            HiddenType::class,
            [
                'data'   => $routeRedirection,
                'mapped' => false,
            ]
        );

        //Ajout d'un champ hidden pour récupérer les routes de redirection dans le controleur à la validation
        $builder->add(
            'idSession',
            HiddenType::class,
            [
                'data'   => isset($options['label_attr']['idSession']) && !is_null($options['label_attr']['idSession'])
                    ? $options['label_attr']['idSession'] : 0,
                'mapped' => false,
            ]
        );

        if ($questionnaire->isOccurrenceMultiple() && null === $occurrence) {
            if (is_null($options['label_attr']['paramId'])) {
                $occurrence = $this->occurrenceManager->getDerniereOccurrenceByQuestionnaireAndUser(
                    $questionnaire,
                    $user
                );

                if (null === $occurrence) {
                    $occurrence = $this->occurrenceManager->createEmpty();
                    $occurrence->setQuestionnaire($questionnaire);
                    $occurrence->setUser($user);
                    $this->occurrenceManager->save($occurrence);
                }
            } else {
                $occurrence = null;
            }
        }

        //Récupération du questionnaire
        $questions = $this->managerQuestionnaire->getQuestionsReponses(
            $idQuestionnaire,
            $idUser,
            $occurrence,
            (isset($options['label_attr']['paramId']) ? $options['label_attr']['paramId'] : null)
        );

        //Construction du formulaire en fonction des questions + chargement des réponses si il y en a
        $this->constructBuilder($builder, $questions, $questionnaire, $occurrence, $options);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'nodevo_questionnaire_questionnaire';
    }

    /**
     * Fonction permettant de créer les champs du formulaire en fonction des questions / réponses passé en param.
     *
     * @param FormBuilderInterface $builder
     * @param                      $questions
     * @param Questionnaire        $questionnaire
     * @param Occurrence|null      $occurrence
     * @param                      $options
     */
    private function constructBuilder(
        FormBuilderInterface $builder,
        $questions,
        Questionnaire $questionnaire,
        Occurrence $occurrence = null,
        $options
    ) {
        if (is_null($options['label_attr']['paramId'])) {
            $this->addOccurrenceType($builder, $questionnaire, $occurrence);
        }

        //Réponse de la question courante
        $reponseCourante = null;

        //Création des questions
        /** @var Question $question */
        foreach ($questions as $question) {
            $reponses = $question->getReponses();
            $reponseCourante = null;

            if (count($reponses) > 0) {
                $reponseCourante = $reponses[0];
                $reponseCourante->setOccurrence($occurrence);
            }

            //Dans le cas où le champ est obligatoire on ajoute automatiquement le contrôle JS dessus
            // il sera surchargé si le champ controle JS est rempli pour la question courante
            $attr = $question->getObligatoire() ? ['class' => 'validate[required]'] : [];

            if (!$options['label_attr']['showAllQuestions'] && $this->isQuestionHidden($question)) {
                continue;
            }

            switch ($question->getTypeQuestion()->getLibelle()) {
                case 'text':
                    $builder->add(
                        $question->getTypeQuestion()->getLibelle()
                        . '_'
                        . $question->getId()
                        . '_'
                        . $question->getAlias(),
                        $question->getTypeQuestion()->getLibelle(),
                        [
                            'max_length' => 255,
                            'required'   => $question->getObligatoire(),
                            'label'      => $question->getLibelle(),
                            'mapped'     => false,
                            'read_only'  => $this->readOnly,
                            'disabled'   => $this->readOnly,
                            'attr'       => is_null($question->getVerifJS()) ? $attr
                                : ['class' => $question->getVerifJS()],
                            'data'       => is_null($reponseCourante) ? '' : $reponseCourante->getReponse(),
                        ]
                    );
                    break;
                case 'checkbox':
                    $attr = $question->getObligatoire() ? ['class' => 'checkbox validate[required]'] : [];

                    $builder->add(
                        $question->getTypeQuestion()->getLibelle()
                        . '_'
                        . $question->getId()
                        . '_'
                        . $question->getAlias(),
                        $question->getTypeQuestion()->getLibelle(),
                        [
                            'required'  => $question->getObligatoire(),
                            'label'     => $question->getLibelle(),
                            'mapped'    => false,
                            'read_only' => $this->readOnly,
                            'disabled'  => $this->readOnly,
                            'attr'      => is_null($question->getVerifJS())
                                ? $attr
                                : [
                                    'class' => 'checkbox ' . $question->getVerifJS(),
                                ],
                            'data'      => is_null($reponseCourante) ? false : ('1' === $reponseCourante->getReponse()),
                        ]
                    );
                    break;
                // Les entity ne sont prévues que pour des entités de Référence
                // (TODO : mettre en base la class et le property ?)
                case 'entity':
                    $builder->add(
                        $question->getTypeQuestion()->getLibelle()
                        . '_'
                        . $question->getId()
                        . '_'
                        . $question->getAlias(),
                        $question->getTypeQuestion()->getLibelle(),
                        [
                            'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                            'choice_label'  => 'libelle',
                            'required'      => $question->getObligatoire(),
                            'label'         => $question->getLibelle(),
                            'mapped'        => false,
                            'read_only'     => $this->readOnly,
                            'disabled'      => $this->readOnly,
                            'empty_value'   => ' - ',
                            'attr'          => $attr,
                            'query_builder' => function (EntityRepository $er) use ($question) {
                                return $er->createQueryBuilder('ref')->where('ref.code = :etat')->setParameter(
                                    'etat',
                                    $question->getReferenceParamTri()
                                )->innerJoin('ref.etat', 'etat', Expr\Join::WITH, 'ref.etat = :actif')->setParameter(
                                    'actif',
                                    Reference::STATUT_ACTIF_ID
                                )->orderBy(
                                    'ref.order',
                                    'ASC'
                                );
                            },
                            'data'          => is_null($reponseCourante) ? null : $reponseCourante->getReference(),
                        ]
                    );
                    break;
                // Les entity ne sont prévues que pour des entités de Référence
                // (TODO : mettre en base la class et le property ?)
                case 'entitymultiple':
                    $attr['class'] = 'select2-multiple-entity';

                    $builder->add(
                        $question->getTypeQuestion()->getLibelle()
                        . '_'
                        . $question->getId()
                        . '_'
                        . $question->getAlias(),
                        'genemu_jqueryselect2_entity',
                        [
                            'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                            'choice_label'  => 'libelle',
                            'required'      => $question->getObligatoire(),
                            'label'         => $question->getLibelle(),
                            'mapped'        => false,
                            'multiple'      => true,
                            'read_only'     => $this->readOnly,
                            'disabled'      => $this->readOnly,
                            'empty_value'   => ' - ',
                            'attr'          => $attr,
                            'query_builder' => function (EntityRepository $er) use ($question) {
                                return $er->createQueryBuilder('ref')->where('ref.code = :etat')->innerJoin(
                                    'ref.etat',
                                    'etat',
                                    Expr\Join::WITH,
                                    'ref.etat = :actif'
                                )
                                ->setParameter('actif', Reference::STATUT_ACTIF_ID)
                                ->setParameter('etat', $question->getReferenceParamTri())
                                ->orderBy('ref.order', 'ASC');
                            },
                            'data' => is_null($reponseCourante) ? null
                                : $reponseCourante->getReferenceMulitple(),
                        ]
                    );
                    break;
                // Les entity ne sont prévues que pour des entités de Référence
                // (@TODO : mettre en base la class et le property ?)
                case 'entityradio':
                    $attr['class'] = 'radio';

                    $builder->add(
                        $question->getTypeQuestion()->getLibelle()
                        . '_'
                        . $question->getId()
                        . '_'
                        . $question->getAlias(),
                        EntityType::class,
                        [
                            'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                            'choice_label'  => 'libelle',
                            'required'      => $question->getObligatoire(),
                            'empty_value'   => $question->getObligatoire() ? false : 'Ne se prononce pas',
                            'label'         => $question->getLibelle(),
                            'mapped'        => false,
                            'read_only'     => $this->readOnly,
                            'disabled'      => $this->readOnly,
                            'expanded'      => true,
                            'multiple'      => false,
                            'attr'          => $attr,
                            'query_builder' => function (EntityRepository $er) use ($question) {
                                return $er->createQueryBuilder('ref')->where('ref.code = :etat')->innerJoin(
                                    'ref.etat',
                                    'etat',
                                    Expr\Join::WITH,
                                    'ref.etat = :actif'
                                )->setParameter('actif', Reference::STATUT_ACTIF_ID)->setParameter(
                                    'etat',
                                    $question->getReferenceParamTri()
                                )->orderBy('ref.order', 'ASC');
                            },
                            'data'          => is_null($reponseCourante) ? null : $reponseCourante->getReference(),
                        ]
                    );
                    break;
                //Entité avec des checkbox
                case 'entitycheckbox':
                    $attr['class'] = 'checkbox';

                    $builder->add(
                        $question->getTypeQuestion()->getLibelle()
                        . '_'
                        . $question->getId()
                        . '_'
                        . $question->getAlias(),
                        EntityType::class,
                        [
                            'class'         => 'HopitalNumeriqueReferenceBundle:Reference',
                            'choice_label'  => 'libelle',
                            'required'      => $question->getObligatoire(),
                            'label'         => $question->getLibelle(),
                            'mapped'        => false,
                            'multiple'      => true,
                            'read_only'     => $this->readOnly,
                            'disabled'      => $this->readOnly,
                            'expanded'      => true,
                            'empty_value'   => ' - ',
                            'attr'          => $attr,
                            'query_builder' => function (EntityRepository $er) use ($question) {
                                return $er->createQueryBuilder('ref')
                                    ->where('ref.code = :etat')
                                    ->innerJoin('ref.etat', 'etat', Expr\Join::WITH, 'ref.etat = :actif')
                                    ->setParameter('actif', Reference::STATUT_ACTIF_ID)
                                    ->setParameter('etat', $question->getReferenceParamTri())
                                    ->orderBy('ref.order', 'ASC')
                                ;
                            },
                            'data'          => is_null($reponseCourante) ? null
                                : $reponseCourante->getReferenceMulitple(),
                        ]
                    );
                    break;
                case 'file':
                    $attr = [];

                    if (is_null($question->getVerifJS()) && $question->getObligatoire()) {
                        $attr['class'] = 'inputUpload validate[required]';
                    } else {
                        $attr['class'] = 'inputUpload ' . $question->getVerifJS();
                    }



                    $questionLabel = $question->getLibelle();
                    if ($question->getAlias() === 'dpi') {
                        $downloadLink = $this->router->generate(
                            'hopitalnumerique_questionnaire_question_download_template',
                            ['question' => $question->getId()]
                        );
                        $questionLabel = $question->getLibelle() . ' <a href="' . $downloadLink . '">Télécharger le modèle</a>';
                        $attr['data-template-enabled'] = 'true';
                    }

                    $fieldName = $question->getTypeQuestion()->getLibelle()
                                 . '_' . $question->getId()
                                 . '_' . $question->getAlias()
                    ;

                    $builder
                        ->add(
                            $fieldName,
                            FileType::class,
                            [
                                'required'   => $question->getObligatoire(),
                                'label'      => $questionLabel,
                                'attr'       => $attr,
                                'mapped'     => false,
                                'read_only'  => $this->readOnly,
                                'disabled'   => $this->readOnly,
                                'data'       => is_null($reponseCourante)
                                    ? null
                                    : [
                                        'id'  => $reponseCourante->getId(),
                                        'lib' => $reponseCourante->getReponse(),
                                    ],
                                'data_class' => null,
                            ]
                        )
                        ->add(
                            $fieldName . '-remove',
                            RadioType::class,
                            [
                                'mapped' => false,
                                'required' => false,
                                'block_name' => 'remove_file',
                            ]
                        )
                    ;
                    break;
                case 'date':
                    if (isset($attr['class'])) {
                        $attr['class'] = $attr['class'] . ' question-type-date';
                    } else {
                        $attr['class'] = 'question-type-date';
                    }
                    if (!is_null($question->getVerifJS())) {
                        $attr['class'] = $attr['class'] . ' ' . $question->getVerifJS();
                    }

                    $builder->add(
                        $question->getTypeQuestion()->getLibelle()
                        . '_'
                        . $question->getId()
                        . '_'
                        . $question->getAlias(),
                        TextType::class,
                        [
                            'required'  => $question->getObligatoire(),
                            'label'     => $question->getLibelle(),
                            'mapped'    => false,
                            'read_only' => $this->readOnly,
                            'disabled'  => $this->readOnly,
                            'attr'      => $attr,
                            'data'      => is_null($reponseCourante) ? '' : $reponseCourante->getReponse(),
                        ]
                    );
                    break;
                case 'interventionobjets':
                    /** @var InterventionDemande $interventionDemande */
                    $interventionDemande = $options['label_attr']['interventionDemande'];

                    $objetsOptions = [];
                    foreach ($interventionDemande->getObjets() as $objet) {
                        $objetsOptions[$objet->getId()] = $objet->getTitre();
                    }

                    $objetIdsSelectionnees = [];
                    $reponse               = $this->managerReponse->findOneBy(
                        [
                            'question' => $question,
                            'user'     => $interventionDemande->getReferent(),
                            'paramId'  => $interventionDemande->getId(),
                        ]
                    );
                    if ($reponse != null) {
                        $objetIdsSelectionnees = explode(',', $reponse->getReponse());
                    } else { // Tout coché par défaut
                        foreach ($interventionDemande->getObjets() as $objet) {
                            $objetIdsSelectionnees[] = $objet->getId();
                        }
                    }

                    $builder->add(
                        $question->getTypeQuestion()->getLibelle()
                        . '_'
                        . $question->getId()
                        . '_'
                        . $question->getAlias(),
                        ChoiceType::class,
                        [
                            'required'  => $question->getObligatoire(),
                            'label'     => $question->getLibelle(),
                            'mapped'    => false,
                            'read_only' => $this->readOnly,
                            'disabled'  => $this->readOnly,
                            'attr'      => is_null($question->getVerifJS())
                                ? $attr
                                : [
                                    'class' => $question->getVerifJS(),
                                ],
                            'choices'   => $objetsOptions,
                            'multiple'  => true,
                            'expanded'  => true,
                            'data'      => $objetIdsSelectionnees,
                        ]
                    );
                    break;
                case 'textarea':
                    $attr['rows'] = 9;

                    $builder->add(
                        $question->getTypeQuestion()->getLibelle()
                        . '_'
                        . $question->getId()
                        . '_'
                        . $question->getAlias(),
                        $question->getTypeQuestion()->getLibelle(),
                        [
                            'required'  => $question->getObligatoire(),
                            'label'     => $question->getLibelle(),
                            'mapped'    => false,
                            'read_only' => $this->readOnly,
                            'disabled'  => $this->readOnly,
                            'attr'      => is_null($question->getVerifJS())
                                ? $attr
                                : [
                                    'rows'  => '9',
                                    'class' => $question->getVerifJS(),
                                ],
                            'data'      => is_null($reponseCourante) ? '' : $reponseCourante->getReponse(),
                        ]
                    );
                    break;
                case 'wysiwyg':
                    if ($question->getObligatoire()) {
                        $constraints = [
                            new NotNull(),
                        ];
                    } else {
                        $constraints = [];
                    }
                    $builder->add(
                        $question->getTypeQuestion()->getLibelle()
                        . '_'
                        . $question->getId()
                        . '_'
                        . $question->getAlias(),
                        CKEditorType::class,
                        [
                            'required'    => $question->getObligatoire(),
                            'label'       => $question->getLibelle(),
                            'trim'        => true,
                            'mapped'      => false,
                            'read_only'   => $this->readOnly,
                            'disabled'    => $this->readOnly,
                            'attr'        => is_null($question->getVerifJS())
                                ? $attr
                                : [
                                    'class' => $question->getVerifJS(),
                                ],
                            'data'        => is_null($reponseCourante) ? '' : $reponseCourante->getReponse(),
                            'config_name' => 'config_questionnaire',
                            'constraints' => $constraints,
                        ]
                    );
                    break;
                case 'etablissement':
                    $etablissementFormModifier = function (
                        FormInterface $form,
                        $full = false
                    ) use (
                        $question,
                        $attr,
                        $reponseCourante
                    ) {
                        $etabAttr = ['class' => 'ajax-select2-list', 'data-url' => '/etablissement/load/'];
                        $fieldOptions = [
                            'class'        => 'HopitalNumeriqueEtablissementBundle:Etablissement',
                            'choice_label' => 'appellation',
                            'required'     => $question->getObligatoire(),
                            'label'        => $question->getLibelle(),
                            'mapped'       => false,
                            'read_only'    => $this->readOnly,
                            'disabled'     => $this->readOnly,
                            'empty_value'  => ' - ',
                            'attr'         => array_merge($attr, $etabAttr),
                            'data'         => is_null($reponseCourante) ? null : $reponseCourante->getEtablissement(),
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
                            $fieldOptions['choices'] =
                                is_null($reponseCourante) || is_null($reponseCourante->getEtablissement())
                                ? []
                                : [$reponseCourante->getEtablissement()]
                            ;
                        }
                        $form->add(
                            $question->getTypeQuestion()->getLibelle()
                            . '_'
                            . $question->getId()
                            . '_'
                            . $question->getAlias(),
                            EntityType::class,
                            $fieldOptions
                        );
                    };

                    $builder->addEventListener(
                        FormEvents::PRE_SET_DATA,
                        function (FormEvent $event) use ($etablissementFormModifier) {
                            $etablissementFormModifier($event->getForm());
                        }
                    );

                    $builder->addEventListener(
                        FormEvents::PRE_SUBMIT,
                        function (FormEvent $event) use ($etablissementFormModifier) {
                            $etablissementFormModifier($event->getForm(), true);
                        }
                    );

                    break;
                // Les entity ne sont prévues que pour des entités de Référence
                // (TODO : mettre en base la class et le property ?)
                case 'etablissementmultiple':
                    $etablissementMultipleFormModifier = function (
                        FormInterface $form,
                        $full = false
                    ) use (
                        $question,
                        $attr,
                        $reponseCourante
                    ) {
                        $etabAttr = ['class' => 'ajax-select2-list', 'data-url' => '/etablissement/load/'];
                        $fieldOptions = [
                            'class'        => 'HopitalNumeriqueEtablissementBundle:Etablissement',
                            'choice_label' => 'appellation',
                            'required'     => $question->getObligatoire(),
                            'label'        => $question->getLibelle(),
                            'mapped'       => false,
                            'multiple'     => true,
                            'read_only'    => $this->readOnly,
                            'disabled'     => $this->readOnly,
                            'empty_value'  => ' - ',
                            'attr'         => array_merge($attr, $etabAttr),
                            'data'         =>
                                is_null($reponseCourante)
                                ? null
                                : $reponseCourante->getEtablissementMulitple()
                            ,
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
                            $fieldOptions['choices'] =
                                is_null($reponseCourante) || is_null($reponseCourante->getEtablissement())
                                ? []
                                : $reponseCourante->getEtablissementMulitple()
                            ;
                        }

                        $form->add(
                            $question->getTypeQuestion()->getLibelle()
                            . '_'
                            . $question->getId()
                            . '_'
                            . $question->getAlias(),
                            EntityType::class,
                            $fieldOptions
                        );
                    };

                    $builder->addEventListener(
                        FormEvents::PRE_SET_DATA,
                        function (FormEvent $event) use ($etablissementMultipleFormModifier) {
                            $etablissementMultipleFormModifier($event->getForm());
                        }
                    );

                    $builder->addEventListener(
                        FormEvents::PRE_SUBMIT,
                        function (FormEvent $event) use ($etablissementMultipleFormModifier) {
                            $etablissementMultipleFormModifier($event->getForm(), true);
                        }
                    );
                    break;
                case 'commentaire':
                    $builder->add(
                        $question->getTypeQuestion()->getLibelle()
                        . '_'
                        . $question->getId()
                        . '_'
                        . $question->getAlias(),
                        NodevoCommentaireType::class,
                        [
                            'data'     => $question->getCommentaire(),
                            'mapped'   => false,
                            'label'    => ' ',
                            'required' => false,
                        ]
                    );
                    break;
                default:
                    $builder->add(
                        $question->getTypeQuestion()->getLibelle()
                        . '_'
                        . $question->getId()
                        . '_'
                        . $question->getAlias(),
                        $question->getTypeQuestion()->getLibelle(),
                        [
                            'required'  => $question->getObligatoire(),
                            'label'     => $question->getLibelle(),
                            'mapped'    => false,
                            'read_only' => $this->readOnly,
                            'disabled'  => $this->readOnly,
                            'attr'      => is_null($question->getVerifJS())
                                ? $attr
                                : [
                                    'class' => $question->getVerifJS(),
                                ],
                            'data'      => is_null($reponseCourante) ? '' : $reponseCourante->getReponse(),
                        ]
                    );
                    break;
            }
        }
    }

    /**
     * @param Question $question
     *
     * @return bool
     */
    private function isQuestionHidden(Question $question)
    {
        //Récupère les classes de la question
        $classes = explode(' ', $question->getVerifJS());
        //Vérifie si on doit afficher la question
        $hide = in_array('hideQuestion', $classes);

        //Si la question est à cacher alors on ne la créée pas
        if ($hide) {
            return true;
        }

        return false;
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $questions = $form->getData()->getQuestions();
        $order = [];

        /** @var Question $question */
        foreach ($questions as $question) {
            if ($this->isQuestionHidden($question)) {
                continue;
            }

            $order[$question->getOrdre()] =
                $question->getTypeQuestion()->getLibelle() . '_' . $question->getId() . '_' . $question->getAlias()
            ;
        }

        $view->vars['order'] = $order;
    }

    /**
     * Ajoute le sous-formulaire de l'occurrence si nécessaire.
     *
     * @param FormBuilderInterface $builder
     * @param Questionnaire        $questionnaire
     * @param Occurrence|null      $occurrence
     */
    private function addOccurrenceType(
        FormBuilderInterface &$builder,
        Questionnaire $questionnaire,
        Occurrence $occurrence = null
    ) {
        if ($questionnaire->isOccurrenceMultiple()) {
            $builder->add(
                'occurrence',
                $this->occurrenceForm,
                [
                    'data'   => $occurrence,
                    'mapped' => false,
                ]
            );
        }
    }
}
