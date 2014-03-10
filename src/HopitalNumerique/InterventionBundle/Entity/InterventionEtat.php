<?php
/**
 * Entité d'état d'une demande d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Entity;

/**
 * Entité d'état d'une demande d'intervention.
 */
class InterventionEtat
{
    /**
     * @var integer ID de l'état Demande initiale
     */
    private static $INTERVENTION_ETAT_DEMANDE_INITIALE = 14;
    /**
     * @var integer ID de l'état Mise en attente par le CMSI
     */
    private static $INTERVENTION_ETAT_ATTENTE_CMSI = 15;
    /**
     * @var integer ID de l'état Refusé par le CMSI
     */
    private static $INTERVENTION_ETAT_REFUS_CMSI = 16;
    /**
     * @var integer ID de l'état Accepté par le CMSI
     */
    private static $INTERVENTION_ETAT_ACCEPTATION_CMSI = 17;
    /**
     * @var integer ID de l'état Accepté par le CMSI - 1ère relance ambassadeur
     */
    private static $INTERVENTION_ETAT_ACCEPTATION_CMSI_RELANCE_1 = 18;
    /**
     * @var integer ID de l'état Accepté par le CMSI - 2nde relance ambassadeur
     */
    private static $INTERVENTION_ETAT_ACCEPTATION_CMSI_RELANCE_2 = 19;
    /**
     * @var integer ID de l'état Refusé par l'ambassadeur
     */
    private static $INTERVENTION_ETAT_REFUS_AMBASSADEUR = 20;
    /**
     * @var integer ID de l'état Accepté par l'ambassadeur
     */
    private static $INTERVENTION_ETAT_ACCEPTATION_AMBASSADEUR = 21;
    /**
     * @var integer ID de l'état Terminée
     */
    private static $INTERVENTION_ETAT_TERMINE = 22;
    /**
     * @var integer ID de l'état Clôturée
     */
    private static $INTERVENTION_ETAT_CLOTURE = 23;
    
    /**
     * Retourne l'ID de la référence correspondant à Demande initiale.
     * 
     * @return integer ID de la référence correspondant à Demande initiale
     */
    public static function getInterventionEtatDemandeInitialeId()
    {
        return self::$INTERVENTION_ETAT_DEMANDE_INITIALE;
    }
    /**
     * Retourne l'ID de la référence correspondant à Mise en attente par le CMSI.
     * 
     * @return integer ID de la référence correspondant à Mise en attente par le CMSI
     */
    public static function getInterventionEtatAttenteCmsiId()
    {
        return self::$INTERVENTION_ETAT_ATTENTE_CMSI;
    }
    /**
     * Retourne l'ID de la référence correspondant à Refusé par le CMSI.
     * 
     * @return integer ID de la référence correspondant à Refusé par le CMSI
     */
    public static function getInterventionEtatRefusCmsiId()
    {
        return self::$INTERVENTION_ETAT_REFUS_CMSI;
    }
    /**
     * Retourne l'ID de la référence correspondant à Accepté par le CMSI.
     * 
     * @return integer ID de la référence correspondant à Accepté par le CMSI
     */
    public static function getInterventionEtatAcceptationCmsiId()
    {
        return self::$INTERVENTION_ETAT_ACCEPTATION_CMSI;
    }
    /**
     * Retourne l'ID de la référence correspondant à Accepté par le CMSI - 1ère relance ambassadeur.
     * 
     * @return integer ID de la référence correspondant à Accepté par le CMSI - 1ère relance ambassadeur
     */
    public static function getInterventionEtatAcceptationCmsiRelance1Id()
    {
        return self::$INTERVENTION_ETAT_ACCEPTATION_CMSI_RELANCE_1;
    }
    /**
     * Retourne l'ID de la référence correspondant à Accepté par le CMSI - 2nde relance ambassadeur.
     * 
     * @return integer ID de la référence correspondant à Accepté par le CMSI - 2nde relance ambassadeur
     */
    public static function getInterventionEtatAcceptationCmsiRelance2Id()
    {
        return self::$INTERVENTION_ETAT_ACCEPTATION_CMSI_RELANCE_2;
    }
    /**
     * Retourne l'ID de la référence correspondant à Refusé par l'ambassadeur.
     * 
     * @return integer ID de la référence correspondant à Refusé par l'ambassadeur
     */
    public static function getInterventionEtatRefusAmbassadeurId()
    {
        return self::$INTERVENTION_ETAT_REFUS_AMBASSADEUR;
    }
    /**
     * Retourne l'ID de la référence correspondant à Accepté par l'ambassadeur.
     * 
     * @return integer ID de la référence correspondant à Accepté par l'ambassadeur
     */
    public static function getInterventionEtatAcceptationAmbassadeurId()
    {
        return self::$INTERVENTION_ETAT_ACCEPTATION_AMBASSADEUR;
    }
    /**
     * Retourne l'ID de la référence correspondant à Terminée.
     * 
     * @return integer ID de la référence correspondant à Terminée
     */
    public static function getInterventionEtatTermineId()
    {
        return self::$INTERVENTION_ETAT_TERMINE;
    }
    /**
     * Retourne l'ID de la référence correspondant à Clôturée.
     * 
     * @return integer ID de la référence correspondant à Clôturée
     */
    public static function getInterventionEtatClotureId()
    {
        return self::$INTERVENTION_ETAT_CLOTURE;
    }
}
