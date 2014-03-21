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
     * @var integer Nombre de jours que le CMSI a pour mettre à jour une demande initiale avant qu'elle soit automatiquement validée
     */
    public static $VALIDATION_CMSI_NOMBRE_JOURS = 7;
    /**
     * @var integer Nombre de jours avant que le CMSI reçoit une alerte s'il n'a pas mis à jour une demande d'intervention en attente
     */
    public static $NOTIFICATION_MISE_EN_ATTENTE_CMSI_NOMBRE_JOURS = 7;
    /**
     * @var integer Nombre de jours avant que l'ambassadeur ne reçoive une première alerte après que la demande ait été validée par le CMSI
     */
    public static $NOTIFICATION_AVANT_RELANCE_AMBASSADEUR_1_NOMBRE_JOURS = 7;
    /**
     * @var integer Nombre de jours avant que l'ambassadeur ne reçoive une seconde alerte après que la demande ait été validée par le CMSI
     */
    public static $NOTIFICATION_AVANT_RELANCE_AMBASSADEUR_2_NOMBRE_JOURS = 7;
    /**
     * @var integer Nombre de jours avant que la demande d'intervention, sans réponse de l'ambassadeur, soit clôturée.
     */
    public static $NOTIFICATION_AVANT_RELANCE_AMBASSADEUR_CLOTURE_NOMBRE_JOURS = 7;
    
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
     * @var integer ID de l'état Demande annulée
     */
    private static $INTERVENTION_ETAT_ANNULATION_ETABLISSEMENT = 307;

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
    /**
     * Retourne l'ID de la référence correspondant à Demande annulée.
     *
     * @return integer ID de la référence correspondant à Demande annulée
     */
    public static function getInterventionEtatAnnulationEtablissementId()
    {
        return self::$INTERVENTION_ETAT_ANNULATION_ETABLISSEMENT;
    }
}
