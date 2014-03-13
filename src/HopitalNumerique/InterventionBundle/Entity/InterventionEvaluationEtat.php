<?php
/**
 * Entité d'état d'une évaluation d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Entity;

/**
 * Entité d'état d'une évaluation d'intervention.
 */
class InterventionEvaluationEtat
{
    /**
     * @var integer ID de l'état En attente
     */
    private static $INTERVENTION_EVALUATION_ETAT_ATTENTE = 27;
    /**
     * @var integer ID de l'état À évaluer
     */
    private static $INTERVENTION_EVALUATION_ETAT_A_EVALUER = 28;
    /**
     * @var integer ID de l'état Évalué
     */
    private static $INTERVENTION_EVALUATION_ETAT_EVALUE = 29;

    /**
     * Retourne l'ID de la référence correspondant à En attente.
     * 
     * @return integer ID de la référence correspondant à En attente
     */
    public static function getInterventionEvaluationEtatAttenteId()
    {
        return self::$INTERVENTION_EVALUATION_ETAT_ATTENTE;
    }
    /**
     * Retourne l'ID de la référence correspondant à À évaluer.
     * 
     * @return integer ID de la référence correspondant à À évaluer
     */
    public static function getInterventionEvaluationEtatAEvaluerId()
    {
        return self::$INTERVENTION_EVALUATION_ETAT_A_EVALUER;
    }
    /**
     * Retourne l'ID de la référence correspondant à Évalué.
     * 
     * @return integer ID de la référence correspondant à Évalué
     */
    public static function getInterventionEvaluationEtatEvalueId()
    {
        return self::$INTERVENTION_EVALUATION_ETAT_EVALUE;
    }
}
