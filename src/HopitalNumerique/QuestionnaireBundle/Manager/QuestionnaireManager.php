<?php

namespace HopitalNumerique\QuestionnaireBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Contractualisation.
 */
class QuestionnaireManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire';

    protected static $_questionnaireArray = array(
    	'expert'     => 1,
    	'ambassadeur' => 2
    );
    
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
    public static function _getQuestionnaireId($label)
    {
        if(key_exists($label, self::$_questionnaireArray))
            return self::$_questionnaireArray[$label];
        else 
             throw new \Exception('Le label \''. $label .'\' ne correspond à aucun questionnaire dans le QuestionnaireManager. Liste des labels attentu : ' . self::_getLabelsQuestionnaire() );
    }
    
    /**
     * Permet l'affichage des labels des questionnaires
     * 
     * @return string
     */
    public static function _getLabelsQuestionnaire()
    {
        //Variable de return
        $res = '';
        
        foreach (self::$_questionnaireArray as $label => $id)
        {
            $res .= '\'' . $label . '\' ';
        }
        
        return $res;
    }
}