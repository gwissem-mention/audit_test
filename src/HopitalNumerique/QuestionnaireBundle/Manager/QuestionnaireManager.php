<?php

namespace HopitalNumerique\QuestionnaireBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;

/**
 * Manager de l'entité Contractualisation.
 */
class QuestionnaireManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire';

    protected $_questionnaireArray = array();
    	
    /**
     * Constructeur du manager
     *
     * @param EntityManager $em Entity Manager de Doctrine
     */
    public function __construct( EntityManager $em, $options = array() )
    {
        parent::__construct($em);
        $this->_questionnaireArray = isset($options['idRoles']) ? $options['idRoles'] : array();
    }
    
    public function getQuestionsReponses( $idQuestionnaire, $idUser )
    {
        return $this->getRepository()->getQuestionsReponses( $idQuestionnaire , $idUser );
    }
    
    /**
     * Id du questionnaire
     * 
     * @param string $label Nom du questionnaire
     * @return id du questionnaire si il existe, sinon 0
     */
    public function getQuestionnaireId($label)
    {
        if(key_exists($label, $this->_questionnaireArray))
            return $this->_questionnaireArray[$label];
        else 
             throw new \Exception('Le label \''. $label .'\' ne correspond à aucun questionnaire dans le QuestionnaireManager. Liste des labels attentu : ' . self::_getLabelsQuestionnaire() );
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
}