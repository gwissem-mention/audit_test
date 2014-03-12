<?php

namespace HopitalNumerique\QuestionnaireBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\File\File;

class QuestionnaireType extends AbstractType
{
    private $idQuestionnaire;
    private $_readOnly = false;
    private $_managerReponse;
    private $_managerQuestion;
    private $_managerQuestionnaire;
    private $_constraints = array();

    /**
     * @param HopitalNumerique\QuestionnaireBundle\Manager\ReponseManager       $managerReponse
     * @param HopitalNumerique\QuestionnaireBundle\Manager\QuestionManager      $managerQuestion
     * @param HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager $managerQuestionnaire
     * @param Validator $validator
     */
    public function __construct($managerReponse, $managerQuestion, $managerQuestionnaire, $validator)
    {
        $this->_managerReponse       = $managerReponse;
        $this->_managerQuestion      = $managerQuestion;
        $this->_managerQuestionnaire = $managerQuestionnaire;
        $this->_constraints          = $managerQuestionnaire->getConstraints( $validator );
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
        $idUser = (isset($options['label_attr']['idUser']) && !is_null($options['label_attr']['idUser'])) ? $options['label_attr']['idUser'] : 0;
        $idQuestionnaire = (isset($options['label_attr']['idQuestionnaire']) && !is_null($options['label_attr']['idQuestionnaire'])) ? $options['label_attr']['idQuestionnaire'] : 0;        
        $questionnaire = $this->_managerQuestionnaire->findOneBy(array('id' => $idQuestionnaire));
        
       /*
        * Tableau de la route de redirection sous la forme :
        * array(
        *   'sauvegarde' => array( 'route' => nom_de_ma_route, 'arguments' => array ('keyArgument' => valueArgument))
        *   'quit'       => array( 'route' => nom_de_ma_route, 'arguments' => array ('keyArgument' => valueArgument))
        *  )
        **/
        $routeRedirection = (isset($options['label_attr']['routeRedirection']) && !is_null($options['label_attr']['routeRedirection'])) ? $options['label_attr']['routeRedirection'] : array();
        $this->_readOnly = (isset($options['label_attr']['readOnly']) && !is_null($options['label_attr']['readOnly'])) ? $options['label_attr']['readOnly'] : false;
        
        //Ajout d'un champ hidden pour récupérer les routes de redirection dans le controleur à la validation
        $builder->add('routeRedirect', 'hidden', array(
                'data'       => $routeRedirection,
                'mapped'     => false
        ));
        
        //Récupération du questionnaire
        $questions = $this->_managerQuestionnaire->getQuestionsReponses($idQuestionnaire, $idUser);

        //Construction du formulaire en fonction des questions + chargement des réponses si il y en a
        $builder = $this->_constructBuilder($builder, $questions, $questionnaire); 
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
     * 
     * @return FormBuilderInterface Le builder avec tous les champs
     */
    private function _constructBuilder(FormBuilderInterface $builder, $questions, $questionnaire)
    {        
        //Réponse de la question courante
        $reponseCourante;
        
        //Création des questions
        foreach ($questions as $question)
        {                    
            $reponses = $question->getReponses();
            $reponseCourante = $reponses[0];
            
            //Dans le cas où le champ est obligatoire on ajoute automatiquement le contrôle JS dessus
            // il sera surchargé si le champ controle JS est rempli pour la question courante
            $attr = $question->getObligatoire() ? array('class' => 'validate[required]') : array();
        
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
            	            'data'       => is_null($reponseCourante) ? false : ('1' === $reponseCourante->getReponse() ? true : false)
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
            	                ->orderBy('ref.order', 'ASC');
            	            },
            	            'data'        => is_null($reponseCourante) ? null : $reponseCourante->getReference()
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
}
