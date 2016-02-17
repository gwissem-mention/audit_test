<?php

namespace HopitalNumerique\QuestionnaireBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use HopitalNumerique\QuestionnaireBundle\Manager\OccurrenceManager;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;
use HopitalNumerique\QuestionnaireBundle\Entity\Occurrence;
use HopitalNumerique\UserBundle\Entity\User;
use Doctrine\ORM\Query\Expr;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

class QuestionnaireType extends AbstractType
{
    /**
     * @var \HopitalNumerique\QuestionnaireBundle\Form\Type\OccurrenceType Formulaire OccurrenceType
     */
    private $occurrenceForm;

    private $_readOnly = false;
    private $_managerReponse;

    /**
     * @var \HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager QuestionnaireManager
     */
    private $_managerQuestionnaire;

    /**
     * @var \HopitalNumerique\QuestionnaireBundle\Manager\OccurrenceManager OccurrenceManager
     */
    private $occurrenceManager;

    /**
     * @var \HopitalNumerique\UserBundle\Manager\UserManager UserManager
     */
    private $userManager;


    /**
     * @param HopitalNumerique\QuestionnaireBundle\Manager\ReponseManager       $managerReponse
     * @param HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager $managerQuestionnaire
     */
    public function __construct(OccurrenceType $occurrenceForm, $managerReponse, $managerQuestionnaire, OccurrenceManager $occurrenceManager, UserManager $userManager)
    {
        $this->occurrenceForm = $occurrenceForm;
        $this->_managerReponse       = $managerReponse;
        $this->_managerQuestionnaire = $managerQuestionnaire;
        $this->occurrenceManager = $occurrenceManager;
        $this->userManager = $userManager;
    }

    /**
     * Ajout des éléments dans le formulaire, spécifie les labels, les widgets utilisés ainsi que l'obligation
     *
     * @param  FormBuilderInterface $builder Le builder contient les champs du formulaire
     * @param  array                $options Data passée au formulaire
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $idUser          = (isset($options['label_attr']['idUser']) && !is_null($options['label_attr']['idUser'])) ? $options['label_attr']['idUser'] : 0;
        $idQuestionnaire = (isset($options['label_attr']['idQuestionnaire']) && !is_null($options['label_attr']['idQuestionnaire'])) ? $options['label_attr']['idQuestionnaire'] : 0;
        $occurrence = (isset($options['label_attr']['occurrence']) ? $options['label_attr']['occurrence'] : null);
        $questionnaire   = $this->_managerQuestionnaire->findOneBy(array('id' => $idQuestionnaire));
        $user = $this->userManager->findOneById($idUser);

       /*
        * Tableau de la route de redirection sous la forme :
        * array(
        *   'sauvegarde' => array( 'route' => nom_de_ma_route, 'arguments' => array ('keyArgument' => valueArgument))
        *   'quit'       => array( 'route' => nom_de_ma_route, 'arguments' => array ('keyArgument' => valueArgument))
        *  )
        **/
        $routeRedirection = (isset($options['label_attr']['routeRedirection']) && !is_null($options['label_attr']['routeRedirection'])) ? $options['label_attr']['routeRedirection'] : array();
        $this->_readOnly  = (isset($options['label_attr']['readOnly']) && !is_null($options['label_attr']['readOnly'])) ? $options['label_attr']['readOnly'] : false;

        //Si le showAllQuestions n'est pas reinseigné, par défaut on les affiches toutes
        if((!isset($options['label_attr']['showAllQuestions']) || is_null($options['label_attr']['showAllQuestions'])))
            $options['label_attr']['showAllQuestions'] = true;

        //Ajout d'un champ hidden pour récupérer les routes de redirection dans le controleur à la validation
        $builder->add('routeRedirect', 'hidden', array(
            'data'       => $routeRedirection,
            'mapped'     => false
        ));

        //Ajout d'un champ hidden pour récupérer les routes de redirection dans le controleur à la validation
        $builder->add('idSession', 'hidden', array(
            'data'       => isset($options['label_attr']['idSession']) && !is_null($options['label_attr']['idSession']) ? $options['label_attr']['idSession'] : 0,
            'mapped'     => false
        ));

        if ($questionnaire->isOccurrenceMultiple() && null === $occurrence)
        {
            $occurrence = $this->occurrenceManager->getDerniereOccurrenceByQuestionnaireAndUser($questionnaire, $user);

            if (null === $occurrence)
            {
                $occurrence = $this->occurrenceManager->createEmpty();
                $occurrence->setQuestionnaire($questionnaire);
                $occurrence->setUser($user);
                $this->occurrenceManager->save($occurrence);
            }
        }

        //Récupération du questionnaire
        $questions = $this->_managerQuestionnaire->getQuestionsReponses($idQuestionnaire, $idUser, $occurrence, (isset($options['label_attr']['paramId']) ? $options['label_attr']['paramId'] : null));

        //Construction du formulaire en fonction des questions + chargement des réponses si il y en a
        $builder = $this->constructBuilder($builder, $questions, $questionnaire, $occurrence, $options);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire'
        ));
    }

    public function getName()
    {
        return 'nodevo_questionnaire_questionnaire';
    }


    /**
     * Fonction permettant de créer les champs du formulaire en fonction des questions / réponses passé en param
     *
     * @param FormBuilderInterface $builder Le builder contient les champs du formulaire
     * @param HopitalNumerique\QuestionnaireBundle\Entity\Question[] $questions
     * @param HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire $questionnaire
     * @param  array                $options Data passée au formulaire
     *
     * @return FormBuilderInterface Le builder avec tous les champs
     */
    private function constructBuilder(FormBuilderInterface $builder, $questions, Questionnaire $questionnaire, Occurrence $occurrence = null, $options)
    {
        $this->addOccurrenceType($builder, $questionnaire, $occurrence);

        //Réponse de la question courante
        $reponseCourante = null;

        //Création des questions
        foreach ($questions as $question)
        {
            $reponses = $question->getReponses();
            $reponseCourante = null;

            if (count($reponses) > 0)
            {
                $reponseCourante = $reponses[0];
                $reponseCourante->setOccurrence($occurrence);
            }

            //Dans le cas où le champ est obligatoire on ajoute automatiquement le contrôle JS dessus
            // il sera surchargé si le champ controle JS est rempli pour la question courante
            $attr = $question->getObligatoire() ? array('class' => 'validate[required]') : array();

            if(!$options['label_attr']['showAllQuestions'])
            {
                //Récupère les classes de la question
                $classes = explode(' ', $question->getVerifJS());
                //Vérifie si on doit afficher la question
                $hide = in_array('hideQuestion', $classes);

                //Si la question est à cacher alors on ne la créée pas
                if($hide)
                    break;
            }

            switch ($question->getTypeQuestion()->getLibelle())
            {
            	case 'text':
            	    $builder->add($question->getTypeQuestion()->getLibelle() . '_' . $question->getId(). '_' . $question->getAlias(), $question->getTypeQuestion()->getLibelle(), array(
            	            'max_length' => 255,
            	            'required'   => $question->getObligatoire(),
            	            'label'      => $question->getLibelle(),
            	            'mapped'     => false,
            	            'read_only'  => $this->_readOnly,
            	            'disabled'   => $this->_readOnly,
            	            'attr'       => is_null($question->getVerifJS()) ? $attr : array('class' => $question->getVerifJS() ),
            	            'data'       => is_null($reponseCourante) ? '' : $reponseCourante->getReponse()
            	    ));
            	    break;
            	case 'checkbox':
                    $attr = $question->getObligatoire() ? array('class' => 'checkbox validate[required]') : array();

            	    $builder->add($question->getTypeQuestion()->getLibelle() . '_' . $question->getId(). '_' . $question->getAlias(), $question->getTypeQuestion()->getLibelle(), array(
            	            'required'   => $question->getObligatoire(),
            	            'label'      => $question->getLibelle(),
            	            'mapped'     => false,
            	            'read_only'  => $this->_readOnly,
            	            'disabled'   => $this->_readOnly,
            	            'attr'       => is_null($question->getVerifJS()) ? $attr : array('class' => 'checkbox ' . $question->getVerifJS() ),
            	            'data'       => is_null($reponseCourante) ? false : ('1' === $reponseCourante->getReponse())
            	    ));
            	    break;
            	//Les entity ne sont prévues que pour des entités de Référence (TODO : mettre en base la class et le property ?)
            	case 'entity':
            	    $builder->add($question->getTypeQuestion()->getLibelle() . '_' . $question->getId(). '_' . $question->getAlias(), $question->getTypeQuestion()->getLibelle(), array(
            	            'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
            	            'property'    => 'libelle',
            	            'required'    => $question->getObligatoire(),
            	            'label'       => $question->getLibelle(),
            	            'mapped'      => false,
            	            'read_only'   => $this->_readOnly,
            	            'disabled'    => $this->_readOnly,
            	            'empty_value' => ' - ',
            	            'attr'        => $attr,
            	            'query_builder' => function(EntityRepository $er) use ($question){
            	                return $er->createQueryBuilder('ref')
            	                ->where('ref.code = :etat')
            	                ->setParameter('etat', $question->getReferenceParamTri())
                                    ->innerJoin('ref.etat', 'etat', Expr\Join::WITH, 'ref.etat = :actif')
                                    ->setParameter('actif', Reference::STATUT_ACTIF_ID )
            	                ->orderBy('ref.order', 'ASC');
            	            },
            	            'data'        => is_null($reponseCourante) ? null : $reponseCourante->getReference()
            	    ));
            	    break;
                //Les entity ne sont prévues que pour des entités de Référence (TODO : mettre en base la class et le property ?)
                case 'entitymultiple':
                    $attr['class'] = 'select2-multiple-entity';

                    $builder->add($question->getTypeQuestion()->getLibelle() . '_' . $question->getId(). '_' . $question->getAlias(), 'genemu_jqueryselect2_entity', array(
                            'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                            'property'    => 'libelle',
                            'required'    => $question->getObligatoire(),
                            'label'       => $question->getLibelle(),
                            'mapped'      => false,
                            'multiple'    => true,
                            'read_only'   => $this->_readOnly,
                            'disabled'    => $this->_readOnly,
                            'empty_value' => ' - ',
                            'attr'        => $attr,
                            'query_builder' => function(EntityRepository $er) use ($question){
                                return $er->createQueryBuilder('ref')
                                ->where('ref.code = :etat')
                                    ->innerJoin('ref.etat', 'etat', Expr\Join::WITH, 'ref.etat = :actif')
                                    ->setParameter('actif', Reference::STATUT_ACTIF_ID )
                                ->setParameter('etat', $question->getReferenceParamTri())
                                ->orderBy('ref.order', 'ASC');
                            },
                            'data'        => is_null($reponseCourante) ? null : $reponseCourante->getReferenceMulitple()
                    ));
                    break;
                //Les entity ne sont prévues que pour des entités de Référence (@TODO : mettre en base la class et le property ?)
                case 'entityradio':
                        $attr['class'] = 'radio';

                    $builder->add($question->getTypeQuestion()->getLibelle() . '_' . $question->getId(). '_' . $question->getAlias(), 'entity', array(
                            'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                            'property'    => 'libelle',
                            'required'    => $question->getObligatoire(),
                            'empty_value' => $question->getObligatoire() ? false : 'Ne se prononce pas',
                            'label'       => $question->getLibelle(),
                            'mapped'      => false,
                            'read_only'   => $this->_readOnly,
                            'disabled'    => $this->_readOnly,
                            'expanded'    => true,
                            'multiple'    => false,
                            'attr'        => $attr,
                            'query_builder' => function(EntityRepository $er) use ($question){
                                return $er->createQueryBuilder('ref')
                                ->where('ref.code = :etat')
                                    ->innerJoin('ref.etat', 'etat', Expr\Join::WITH, 'ref.etat = :actif')
                                    ->setParameter('actif', Reference::STATUT_ACTIF_ID )
                                ->setParameter('etat', $question->getReferenceParamTri())
                                ->orderBy('ref.order', 'ASC');
                            },
                            'data'        => is_null($reponseCourante) ? null : $reponseCourante->getReference()
                    ));
                    break;
                //Entité avec des checkbox
                case 'entitycheckbox':
                        $attr['class'] = 'checkbox';

                    $builder->add($question->getTypeQuestion()->getLibelle() . '_' . $question->getId(). '_' . $question->getAlias(), 'entity', array(
                            'class'       => 'HopitalNumeriqueReferenceBundle:Reference',
                            'property'    => 'libelle',
                            'required'    => $question->getObligatoire(),
                            'label'       => $question->getLibelle(),
                            'mapped'      => false,
                            'multiple'    => true,
                            'read_only'   => $this->_readOnly,
                            'disabled'    => $this->_readOnly,
                            'expanded'    => true,
                            'empty_value' => ' - ',
                            'attr'        => $attr,
                            'query_builder' => function(EntityRepository $er) use ($question){
                                return $er->createQueryBuilder('ref')
                                ->where('ref.code = :etat')
                                    ->innerJoin('ref.etat', 'etat', Expr\Join::WITH, 'ref.etat = :actif')
                                    ->setParameter('actif', Reference::STATUT_ACTIF_ID )
                                ->setParameter('etat', $question->getReferenceParamTri())
                                ->orderBy('ref.order', 'ASC');
                            },
                            'data'        => is_null($reponseCourante) ? null : $reponseCourante->getReferenceMulitple()
                    ));
                    break;
            	case 'file':
                    $attr = $question->getObligatoire() ? array('class' => 'inputUpload validate[required]') : array();
            	    $builder->add($question->getTypeQuestion()->getLibelle() . '_' . $question->getId(). '_' . $question->getAlias(), 'file', array(
            	            'required'   => $question->getObligatoire(),
            	            'label'      => $question->getLibelle(),
                            'attr'       => is_null($question->getVerifJS()) ? $attr : array('class' => 'inputUpload ' . $question->getVerifJS()),
            	            'mapped'     => false,
            	            'read_only'  => $this->_readOnly,
            	            'disabled'   => $this->_readOnly,
            	            'data'       => is_null($reponseCourante) ? null : array('id' => $reponseCourante->getId(), 'lib' => $reponseCourante->getReponse()),
            	            'data_class' => null
            	    ));
            	    break;
            	case 'date':
            	    if (isset($attr['class']))
            	        $attr['class'] = $attr['class'].' question-type-date';
            	    else  $attr['class'] = 'question-type-date';
            	    if (!is_null($question->getVerifJS()))
            	        $attr['class'] = $attr['class'].' '.$question->getVerifJS();

            	    $builder->add($question->getTypeQuestion()->getLibelle() . '_' . $question->getId(). '_' . $question->getAlias(), 'text', array(
        	            'required'   => $question->getObligatoire(),
        	            'label'      => $question->getLibelle(),
        	            'mapped'     => false,
        	            'read_only'  => $this->_readOnly,
        	            'disabled'   => $this->_readOnly,
        	            'attr'       => $attr,
        	            'data'       => is_null($reponseCourante) ? '' : $reponseCourante->getReponse()
            	    ));
            	    break;
            	case 'interventionobjets':

            	    $interventionDemande = $options['label_attr']['interventionDemande'];

            	    $objetsOptions = array();
            	    foreach ($interventionDemande->getObjets() as $objet)
                    {
            	        $objetsOptions[$objet->getId()] = $objet->getTitre();
                    }

            	    $objetIdsSelectionnees = array();
            	    $reponses = $this->_managerReponse->reponsesByQuestionnaireByUser($questionnaire->getId(), $interventionDemande->getReferent()->getId(), true, null, $interventionDemande->getId());
            	    $reponse = $this->_managerReponse->findOneBy(array(
            	        'question' => $question,
            	        'user' => $interventionDemande->getReferent(),
            	        'paramId' => $interventionDemande->getId()
            	    ));
            	    if ($reponse != null)
            	    {
            	        $objetIdsSelectionnees = explode(',', $reponse->getReponse());
            	    }
            	    else // Tout coché par défaut
            	    {
            	        foreach ($interventionDemande->getObjets() as $objet)
                        {
            	            $objetIdsSelectionnees[] = $objet->getId();
                        }
            	    }

            	    $builder->add($question->getTypeQuestion()->getLibelle() . '_' . $question->getId(). '_' . $question->getAlias(), 'choice', array(
        	            'required'   => $question->getObligatoire(),
        	            'label'      => $question->getLibelle(),
        	            'mapped'     => false,
        	            'read_only'  => $this->_readOnly,
        	            'disabled'   => $this->_readOnly,
        	            'attr'       => is_null($question->getVerifJS()) ? $attr : array('class' => $question->getVerifJS() ),
        	            'choices'    => $objetsOptions,
            	        'multiple' => true,
        	            'expanded' => true,
            	        'data' => $objetIdsSelectionnees
            	    ));
            	    break;
            	case 'textarea':
            	    $attr['rows'] = 9;

            	    $builder->add($question->getTypeQuestion()->getLibelle() . '_' . $question->getId(). '_' . $question->getAlias(), $question->getTypeQuestion()->getLibelle(), array(
        	            'required'   => $question->getObligatoire(),
        	            'label'      => $question->getLibelle(),
        	            'mapped'     => false,
        	            'read_only'  => $this->_readOnly,
        	            'disabled'   => $this->_readOnly,
        	            'attr'       => is_null($question->getVerifJS()) ? $attr : array('rows' => '9', 'class' => $question->getVerifJS() ),
        	            'data'       => is_null($reponseCourante) ? '' : $reponseCourante->getReponse()
            	    ));
            	    break;
                case 'etablissement':
                    $builder->add($question->getTypeQuestion()->getLibelle() . '_' . $question->getId(). '_' . $question->getAlias(), 'genemu_jqueryselect2_entity', array(
                            'class'       => 'HopitalNumeriqueEtablissementBundle:Etablissement',
                            'property'    => 'appellation',
                            'required'    => $question->getObligatoire(),
                            'label'       => $question->getLibelle(),
                            'mapped'      => false,
                            'read_only'   => $this->_readOnly,
                            'disabled'    => $this->_readOnly,
                            'empty_value' => ' - ',
                            'attr'        => $attr,
                            'query_builder' => function(EntityRepository $er){
                                return $er->createQueryBuilder('eta')
                                ->orderBy('eta.nom', 'ASC');
                            },
                            'data'        => is_null($reponseCourante) ? null : $reponseCourante->getEtablissement()
                    ));
                    break;
                //Les entity ne sont prévues que pour des entités de Référence (TODO : mettre en base la class et le property ?)
                case 'etablissementmultiple':
                    $attr['class'] = 'select2-multiple-entity';

                    $builder->add($question->getTypeQuestion()->getLibelle() . '_' . $question->getId(). '_' . $question->getAlias(), 'genemu_jqueryselect2_entity', array(
                            'class'       => 'HopitalNumeriqueEtablissementBundle:Etablissement',
                            'property'    => 'appellation',
                            'required'    => $question->getObligatoire(),
                            'label'       => $question->getLibelle(),
                            'mapped'      => false,
                            'multiple'    => true,
                            'read_only'   => $this->_readOnly,
                            'disabled'    => $this->_readOnly,
                            'empty_value' => ' - ',
                            'attr'        => $attr,
                            'query_builder' => function(EntityRepository $er){
                                return $er->createQueryBuilder('eta')
                                ->orderBy('eta.nom', 'ASC');
                            },
                            'data'        => is_null($reponseCourante) ? null : $reponseCourante->getEtablissementMulitple()
                    ));
                    break;
                case 'commentaire':
                    $builder->add($question->getTypeQuestion()->getLibelle() . '_' . $question->getId(). '_' . $question->getAlias(), 'nodevocommentaire', array(
                        'data'      => $question->getCommentaire(),
                        'mapped'    => false,
                        'label'     => ' ',
                        'required'  => false
                    ));
                    break;
            	default:
            	    $builder->add($question->getTypeQuestion()->getLibelle() . '_' . $question->getId(). '_' . $question->getAlias(), $question->getTypeQuestion()->getLibelle(), array(
        	            'required'   => $question->getObligatoire(),
        	            'label'      => $question->getLibelle(),
        	            'mapped'     => false,
        	            'read_only'  => $this->_readOnly,
        	            'disabled'   => $this->_readOnly,
        	            'attr'       => is_null($question->getVerifJS()) ? $attr : array('class' => $question->getVerifJS() ),
        	            'data'       => is_null($reponseCourante) ? '' : $reponseCourante->getReponse()
            	    ));
            	    break;
            }
        }
    }

    /**
     * Ajoute le sous-formulaire de l'occurrence si nécessaire.
     *
     * @param \Symfony\Component\Form\FormBuilderInterface               $builder       FormBuilder
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire $questionnaire Questionnaire
     * @param \HopitalNumerique\UserBundle\Entity\User                   $user          Utilisateur
     */
    private function addOccurrenceType(FormBuilderInterface &$builder, Questionnaire $questionnaire, Occurrence $occurrence = null)
    {
        if ($questionnaire->isOccurrenceMultiple())
        {
            $builder
                ->add('occurrence', $this->occurrenceForm, array(
                    'data' => $occurrence,
                    'mapped' => false
                ))
            ;
        }
    }
}
