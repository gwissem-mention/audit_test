<?php

namespace HopitalNumerique\ModuleBundle\Entity;

/**
 * Identifiers of references used to manage status relative to formation (including session and participation).
 */
class SessionStatus
{
    /**
     * Reference ID for formation session status 'Actif'.
     */
    const STATUT_SESSION_FORMATION_ACTIVE_ID = 403;

    /**
     * Reference ID for formation session status 'Inactif'.
     */
    const STATUT_SESSION_FORMATION_UNACTIVE_ID = 404;

    /**
     * Reference ID for formation session status 'Annulé'.
     */
    const STATUT_SESSION_FORMATION_CANCELED_ID = 405;

    /**
     * Reference ID for formation status 'En attente'.
     */
    const STATUT_FORMATION_WAITING_ID = 406;

    /**
     * Reference ID for formation status 'Acceptée'.
     */
    const STATUT_FORMATION_ACCEPTED_ID = 407;

    /**
     * Reference ID for formation status 'Refusée'.
     */
    const STATUT_FORMATION_REFUSED_ID = 408;

    /**
     * Reference ID for formation status 'Annulée'.
     */
    const STATUT_FORMATION_CANCELED_ID = 409;

    /**
     * Reference ID for formation participation status 'En attente'.
     */
    const STATUT_PARTICIPATION_WAITING_ID = 410;

    /**
     * Reference ID for formation participation status 'A participé'.
     */
    const STATUT_PARTICIPATION_OK_ID = 411;

    /**
     * Reference ID for formation participation status 'N'a pas participé'.
     */
    const STATUT_PARTICIPATION_KO_ID = 412;
}
