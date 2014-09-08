<?php

namespace HopitalNumerique\QuestionnaireBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;

/**
 * Manager de l'entité Contractualisation.
 */
class QuestionnaireManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire';

    protected $_questionnaireArray = array();
    protected $_mailExpertReponses = array();
    protected $_mailReponses       = array();
    protected $_managerReponse;
        
    /**
     * Constructeur du manager
     *
     * @param EntityManager $em Entity Manager de Doctrine
     */
    public function __construct( EntityManager $em, $managerReponse, $options = array() )
    {
        parent::__construct($em);
        $this->_questionnaireArray = isset($options['idRoles']) ? $options['idRoles'] : array();
        $this->_mailExpertReponses = isset($options['mailExpertReponses']) ? $options['mailExpertReponses'] : array();
        $this->_mailReponses       = isset($options['mailReponses']) ? $options['mailReponses'] : array();
        $this->_managerReponse     = $managerReponse;
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getDatasForGrid( \StdClass $condition = null )
    {
        return $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();
    }
    
    /**
     * [getQuestionsReponses description]
     *
     * @param  [type] $idQuestionnaire [description]
     * @param  [type] $idUser          [description]
     * @param  [type] $paramId         [description]
     *
     * @return [type]
     */
    public function getQuestionsReponses( $idQuestionnaire, $idUser, $paramId = null )
    {
        return $this->getRepository()->getQuestionsReponses( $idQuestionnaire , $idUser, $paramId );
    }
    
    /**
     * Get les utilisateurs qui ont répondu à ce questionnaire
     *
     * @param  int $idQuestionnaire [description]
     *
     * @return [type]
     */
    public function getQuestionnaireRepondant( $idQuestionnaire )
    {
        return $this->getRepository()->getQuestionnaireRepondant( $idQuestionnaire )->getQuery()->getResult();
    }

    /**
     * Get les adresses mails dans le config.yml/parameter.yml de l'envoies des mails experts
     *
     * @return array( 'adresse' => 'nom' )
     */
    public function getMailExpertReponses()
    {
        return $this->_mailExpertReponses;
    }

    /**
     * Get les adresses mails dans le config.yml/parameter.yml de l'envoies des réponses
     *
     * @return array( 'adresse' => 'nom' )
     */
    public function getMailReponses()
    {
        return $this->_mailReponses;
    }
    
    /**
     * Id du questionnaire
     * 
     * @param string $label Nom du questionnaire
     * @return id du questionnaire si il existe, sinon 0
     */
    public function getQuestionnaireId($label)
    {
        if(array_key_exists($label, $this->_questionnaireArray))
            return $this->_questionnaireArray[$label];
        else 
             throw new \Exception('Le label \''. $label .'\' ne correspond à aucun questionnaire dans le QuestionnaireManager. Liste des labels attentu : ' . self::getLabelsQuestionnaire() );
    }
    
    /**
     * Permet l'affichage des labels des questionnaires
     * 
     * @return string
     */
    public function getLabelsQuestionnaire()
    {
        //Variable de return
        $res = '';
        
        foreach ($this->_questionnaireArray as $label => $id)
        {
            $res .= '\'' . $label . '\' ';
        }
        
        return $res;
    }
    
    /**
     * Renvoie une chaine de caractère correspondant aux données du formulaire soumis
     * 
     * @param array(HopitalNumerique\QuestionnaireBundle\Entity\Reponse) $reponses
     * 
     * @return string Affichage du formulaire
     */
    public function getQuestionnaireFormateMail($reponses)
    {
        $candidature          = '<ul>';

        foreach ($reponses as $key => $reponse)
        {
            switch($reponse->getQuestion()->getTypeQuestion()->getLibelle())
            {
                case 'entityradio':
            	case 'entity':
                    $candidature .= '<li><strong>' . $reponse->getQuestion()->getLibelle() . '</strong> : '; 
                    if(!is_null($reponse->getReference()))
                    {
                        $candidature .= $reponse->getReference()->getLibelle();
                    }
            	    $candidature .= "</li>";
            	    break;
            	case 'checkbox':
            	    $candidature .= '<li><strong>' . $reponse->getQuestion()->getLibelle() . '</strong> : ' . ('1' == $reponse->getReponse() ? 'Oui' : 'Non' ). "</li>";
            	    break;
                //Gestion très sale, à revoir au moment de la construction du tableau de réponses avec des niveaux d'enfants/parents etc.
                case 'entitymultiple':
                case 'entitycheckbox':
                    //Affichage pour une possibilité de plusieurs réponses à cette question
                    $candidature .= "<li><strong>" . $reponse->getQuestion()->getLibelle() . "</strong> : <ul>";
                    foreach ($reponse->getReferenceMulitple() as $key => $referenceMultiple) 
                    {
                        $candidature .=  "<li>";
                        $candidature .= $referenceMultiple->getLibelle();
                        $candidature .= "</li>";
                    }
                    $candidature .= "</ul></li>";
                    break;
            	default:
            	    $candidature .= '<li><strong>' . $reponse->getQuestion()->getLibelle() . '</strong> : ' . $reponse->getReponse() . "</li>";
            	    break;
            }
        }
        $candidature .= '</ul>';
        
        return $candidature;
    }

    /**
     * Créer un tableau formaté pour l'export CSV
     *
     * @param integer $idQuestionnaire ID du questionnaire
     * @param array   $users           Liste des utilisateurs
     *
     * @return array
     */
    public function buildForExport( $idQuestionnaire, $users )
    {
        $questionnaire = $this->findOneBy( array('id' => $idQuestionnaire) );

        //prepare colonnes
        $colonnes = array( 'id' => 'id_utilisateur', 'user' => 'Prénom et Nom de l\'utilisateur' );
        $emptyRow = array( 'id' => '' );
        $questions = $questionnaire->getQuestions();
        foreach($questions as $question){
            if( $question->getTypeQuestion()->getLibelle() != 'file'){
                $colonnes['question'.$question->getId()] = $question->getLibelle();
                $emptyRow['question'.$question->getId()] = '';
            }
        }

        $datas = array();
        foreach($users as $user)
        {
            //prepare user infos
            $row         = array_merge(array(), $emptyRow); //use this to clone the empty table $emptyRow => make sure we have at least an empty data
            $row['id']   = $user->getId();
            $row['user'] = $user->getPrenomNom();

            //get reponses
            $reponses = $this->_managerReponse->reponsesByQuestionnaireByUser( $idQuestionnaire, $user->getId(), true );
            foreach($reponses as $reponse)
            {
                $question = $reponse->getQuestion();

                //on récupère toutes les question sauf les types fichiers
                if( $question->getTypeQuestion()->getLibelle() != 'file')
                {
                    //identifiant de la question
                    $field = 'question'.$question->getId();

                    switch ($question->getTypeQuestion()->getLibelle())
                    {
                        case 'entity':
                            $row[$field] = is_null($reponse->getReference()) ? '-' : $reponse->getReference()->getLibelle();
                            break;
                        case 'entityradio':
                            $row[$field] = is_null($reponse->getReference()) ? '-' : $reponse->getReference()->getLibelle();
                            break;
                        case 'entitymultiple':
                            //Si il y a des réponses à exporter on exporte les libellés des références concaténés
                            if(is_null($reponse->getReferenceMulitple()))
                            {
                                $row[$field] = '-';
                            }
                            else
                            {
                                $lib = '';
                                $compteur = 0;
                                foreach ($reponse->getReferenceMulitple() as $reference) 
                                {
                                    $compteur++;
                                    //Récupération du libellé de la référence + ajout d'un tiret si on est pas à la fin
                                    $lib .= $reference->getLibelle() . ($compteur == count($reponse->getReferenceMulitple()) ? '' : ' - ');
                                }
                                $row[$field] = $lib;
                            }
                            break;
                        case 'entitycheckbox':
                            //Si il y a des réponses à exporter on exporte les libellés des références concaténés
                            if(is_null($reponse->getReferenceMulitple()))
                            {
                                $row[$field] = '-';
                            }
                            else
                            {
                                $lib = '';
                                $compteur = 0;
                                foreach ($reponse->getReferenceMulitple() as $reference) 
                                {
                                    $compteur++;
                                    //Récupération du libellé de la référence + ajout d'un tiret si on est pas à la fin
                                    $lib .= $reference->getLibelle() . ($compteur == count($reponse->getReferenceMulitple()) ? '' : ' - ');
                                }
                                $row[$field] = $lib;
                            }
                            break;
                        case 'checkbox':
                            $row[$field] = ('1' == $reponse->getReponse() ? 'Oui' : 'Non' );
                            break;
                        default:
                            $row[$field] = $reponse->getReponse();
                            break;
                    }
                }
            }

            $datas[] = $row;
        }

        return array('colonnes' => $colonnes, 'datas' => $datas );
    }
}