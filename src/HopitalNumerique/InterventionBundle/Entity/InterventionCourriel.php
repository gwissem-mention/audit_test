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
    const COURRIEL_SOLLICITATION_SANS_ETABLISSEMENT_ID = 62;


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
     * @var integer ID du courriel d'acceptation d'une demande d'acceptation par un ambassadeur
     */
    private static $COURRIEL_INTERVENTION_DEMANDE_EST_ACCEPTEE_AMBASSADEUR_ID = 17;
    /**
     * @var integer ID du courriel de refus d'une demande d'acceptation par un ambassadeur
     */
    private static $COURRIEL_INTERVENTION_DEMANDE_EST_REFUSEE_AMBASSADEUR_ID = 18;
    /**
     * @var integer ID du courriel de relance 1 d'un ambassadeur
     */
    private static $COURRIEL_INTERVENTION_DEMANDE_RELANCE_AMBASSADEUR_1_ID = 19;
    /**
     * @var integer ID du courriel de relance 2 d'un ambassadeur
     */
    private static $COURRIEL_INTERVENTION_DEMANDE_RELANCE_AMBASSADEUR_2_ID = 20;
    /**
     * @var integer ID du courriel de clôture car sans nouvelle de l'ambassadeur
     */
    private static $COURRIEL_INTERVENTION_DEMANDE_RELANCE_AMBASSADEUR_CLOTURE_ID = 21;
    /**
     * @var integer ID du courriel de l'évaluation remplie
     */
    private static $COURRIEL_EVALUATION_REMPLIE_ID = 22;
    /**
     * @var integer ID du courriel d'annulation d'une demande par un établissement
     */
    private static $COURRIEL_INTERVENTION_DEMANDE_EST_ANNULEE_ETABLISSEMENT_ID = 25;
    /**
     * @var integer ID du courriel de relance d'une demande en attente CMSI
     */
    private static $COURRIEL_INTERVENTION_DEMANDE_RELANCE_ATTENTE_CMSI_ID = 27;
    
    
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
    /**
     * Retourne l'ID du courriel d'acceptation d'une demande d'acceptation par un ambassadeur.
     *
     * @return integer ID du courriel d'acceptation d'une demande d'acceptation par un ambassadeur
     */
    public static function getInterventionCourrielEstAccepteeAmbassadeurId()
    {
        return self::$COURRIEL_INTERVENTION_DEMANDE_EST_ACCEPTEE_AMBASSADEUR_ID;
    }
    /**
     * Retourne l'ID du courriel de refus d'une demande d'acceptation par un ambassadeur.
     *
     * @return integer ID du courriel de refus d'une demande d'acceptation par un ambassadeur
     */
    public static function getInterventionCourrielEstRefuseeAmbassadeurId()
    {
        return self::$COURRIEL_INTERVENTION_DEMANDE_EST_REFUSEE_AMBASSADEUR_ID;
    }
    /**
     * Retourne l'ID du courriel de relance 1 pour l'ambassadeur.
     *
     * @return integer ID du courriel de relance 1 pour l'ambassadeur
     */
    public static function getInterventionCourrielRelanceAmbassadeur1Id()
    {
        return self::$COURRIEL_INTERVENTION_DEMANDE_RELANCE_AMBASSADEUR_1_ID;
    }
    /**
     * Retourne l'ID du courriel de relance 2 pour l'ambassadeur.
     *
     * @return integer ID du courriel de relance 2 pour l'ambassadeur
     */
    public static function getInterventionCourrielRelanceAmbassadeur2Id()
    {
        return self::$COURRIEL_INTERVENTION_DEMANDE_RELANCE_AMBASSADEUR_2_ID;
    }
    /**
     * Retourne l'ID du courriel de clôture car sans nouvelle de l'ambassadeur.
     *
     * @return integer ID du courriel de clôture car sans nouvelle de l'ambassadeur
     */
    public static function getInterventionCourrielRelanceAmbassadeurClotureId()
    {
        return self::$COURRIEL_INTERVENTION_DEMANDE_RELANCE_AMBASSADEUR_CLOTURE_ID;
    }
    /**
     * Retourne l'ID du courriel de l'évaluation remplie.
     *
     * @return integer ID du courriel de l'évaluation remplie
     */
    public static function getInterventionCourrielEvaluationRemplieId()
    {
        return self::$COURRIEL_EVALUATION_REMPLIE_ID;
    }
    /**
     * Retourne l'ID du courriel d'annulation d'une demande par un établissement.
     *
     * @return integer ID du courriel d'annulation d'une demande par un établissement
     */
    public static function getInterventionCourrielEstAnnuleeEtablissementId()
    {
        return self::$COURRIEL_INTERVENTION_DEMANDE_EST_ANNULEE_ETABLISSEMENT_ID;
    }
    /**
     * Retourne l'ID du courriel d'annulation d'une demande par un établissement.
     *
     * @return integer ID du courriel d'annulation d'une demande par un établissement
     */
    public static function getInterventionCourrielRelanceAttenteCmsiId()
    {
        return self::$COURRIEL_INTERVENTION_DEMANDE_RELANCE_ATTENTE_CMSI_ID;
    }
}
