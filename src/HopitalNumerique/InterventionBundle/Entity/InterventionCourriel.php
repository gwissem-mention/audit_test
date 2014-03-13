<?php
/**
 * Entité d'un courriel d'une demande d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Entity;
use Nodevo\MailBundle\Entity\Mail;

/**
 * Entité d'un courriel d'une demande d'intervention.
 */
class InterventionCourriel extends Mail
{
    /**
     * @var integer ID du courriel de création d'une demande d'intervention
     */
    private static $COURRIEL_INTERVENTION_DEMANDE_CREATION_ID = 3;
    /**
     * @var integer ID du courriel de demande d'acceptation ou non d'une demande d'intervention par l'ambassadeur
     */
    private static $COURRIEL_INTERVENTION_DEMANDE_ACCEPTATION_AMBASSADEUR_ID = 4;
    /**
     * @var integer ID du courriel de demande d'acceptation ou non d'une demande d'intervention par le CMSI
     */
    private static $COURRIEL_INTERVENTION_DEMANDE_ACCEPTATION_CMSI_ID = 10;
    /**
     * @var integer ID du courriel d'alerte de création de demande d'acceptation émise par un CMSI
     */
    private static $COURRIEL_INTERVENTION_DEMANDE_ALERTE_REFERENT_ID = 5;
    /**
     * @var integer ID du courriel d'acceptation d'une demande d'acceptation par un CMSI
     */
    private static $COURRIEL_INTERVENTION_DEMANDE_EST_ACCEPTEE_CMSI_ID = 6;
    /**
     * @var integer ID du courriel de refus d'une demande d'acceptation par un CMSI
     */
    private static $COURRIEL_INTERVENTION_DEMANDE_EST_REFUSEE_CMSI_ID = 7;
    /**
     * @var integer ID du courriel d'invitation du référent à évaluer une intervention
     */
    private static $COURRIEL_INVITATION_EVALUATION_REFERENT_ID = 15;
    /**
     * @var integer ID du courriel de changement d'ambassadeur
     */
    private static $COURRIEL_INTERVENTION_CHANGEMENT_AMBASSADEUR_ID = 16;

    /**
     * Retourne l'ID du courriel de création d'une demande d'intervention.
     * 
     * @return integer ID du courriel de création d'une demande d'intervention
     */
    public static function getInterventionCourrielCreationId()
    {
        return self::$COURRIEL_INTERVENTION_DEMANDE_CREATION_ID;
    }
    /**
     * Retourne l'ID du courriel d'acceptation ou non d'une demande d'intervention par l'ambassadeur.
     * 
     * @return integer ID du courriel d'acceptation ou non d'une demande d'intervention par l'ambassadeur
     */
    public static function getInterventionCourrielAcceptationAmbassadeurId()
    {
        return self::$COURRIEL_INTERVENTION_DEMANDE_ACCEPTATION_AMBASSADEUR_ID;
    }
    /**
     * Retourne l'ID du courriel d'acceptation ou non d'une demande d'intervention par le CMSI.
     *
     * @return integer ID du courriel d'acceptation ou non d'une demande d'intervention par le CMSI
     */
    public static function getInterventionCourrielAcceptationCmsiId()
    {
        return self::$COURRIEL_INTERVENTION_DEMANDE_ACCEPTATION_CMSI_ID;
    }
    /**
     * Retourne l'ID du courriel d'alerte de création de demande d'acceptation émise par un CMSI.
     * 
     * @return integer ID du courriel d'alerte de création de demande d'acceptation émise par un CMSI
     */
    public static function getInterventionCourrielAlerteReferentId()
    {
        return self::$COURRIEL_INTERVENTION_DEMANDE_ALERTE_REFERENT_ID;
    }
    /**
     * Retourne l'ID du courriel d'acceptation d'une demande d'acceptation par un CMSI.
     *
     * @return integer ID du courriel d'acceptation d'une demande d'acceptation par un CMSI
     */
    public static function getInterventionCourrielEstAccepteeCmsiId()
    {
        return self::$COURRIEL_INTERVENTION_DEMANDE_EST_ACCEPTEE_CMSI_ID;
    }
    /**
     * Retourne l'ID du courriel de refus d'une demande d'acceptation par un CMSI.
     *
     * @return integer ID du courriel de refus d'une demande d'acceptation par un CMSI
     */
    public static function getInterventionCourrielEstRefuseeCmsiId()
    {
        return self::$COURRIEL_INTERVENTION_DEMANDE_EST_REFUSEE_CMSI_ID;
    }
    /**
     * Retourne l'ID du courriel d'invitation du référent à évaluaer une intervention.
     *
     * @return integer ID du courriel d'invitation du référent à évaluaer une intervention
     */
    public static function getInterventionCourrielInvitationEvaluationReferentId()
    {
        return self::$COURRIEL_INVITATION_EVALUATION_REFERENT_ID;
    }
    /**
     * Retourne l'ID du courriel d'invitation du référent à évaluaer une intervention.
     *
     * @return integer ID du courriel d'invitation du référent à évaluaer une intervention
     */
    public static function getInterventionCourrielChangementAmbassadeurId()
    {
        return self::$COURRIEL_INTERVENTION_CHANGEMENT_AMBASSADEUR_ID;
    }
}
