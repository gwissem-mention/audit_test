<?php
namespace HopitalNumerique\InterventionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entité d'une évaluation d'une intervention.
 */
class InterventionEvaluation
{
    private static $EVALUATION_QUESTIONNAIRE_ID = 3;

    /**
     * Retourne l'ID du questionnaire d'évaluation.
     * 
     * @return integer ID du questionnaire d'évaluation
     */
    public static function getEvaluationQuestionnaireId()
    {
        return self::$EVALUATION_QUESTIONNAIRE_ID;
    }
}
