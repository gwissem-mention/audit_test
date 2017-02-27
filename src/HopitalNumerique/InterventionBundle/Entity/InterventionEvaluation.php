<?php

namespace HopitalNumerique\InterventionBundle\Entity;

/**
 * Entité d'une évaluation d'une intervention.
 */
class InterventionEvaluation
{
    /**
     * @var int ID du questionnaire d'évaluation
     */
    private static $EVALUATION_QUESTIONNAIRE_ID = 3;
    /**
     * @var int ID de la question concernant la date de l'intervention
     */
    private static $EVALUATION_QUESTION_INTERVENTION_DATE_ID = 23;

    /**
     * Retourne l'ID du questionnaire d'évaluation.
     *
     * @return int ID du questionnaire d'évaluation
     */
    public static function getEvaluationQuestionnaireId()
    {
        return self::$EVALUATION_QUESTIONNAIRE_ID;
    }

    /**
     * Retourne l'ID de la question concernant la date de l'intervention.
     *
     * @return int ID de la question concernant la date de l'intervention
     */
    public static function getEvaluationQuestionInterventionDateId()
    {
        return self::$EVALUATION_QUESTION_INTERVENTION_DATE_ID;
    }
}
