<?php
/**
 * Classe gérant le type d'état de la dernière demande d'intervention ouverte par l'utilisateur.
 * Cela permet, pour les listes de demandes à onglets, d'ouvrir le bon onglet à l'ouverture de la page.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\DependencyInjection\Demande;

use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;

/**
 * Classe gérant le type d'état de la dernière demande d'intervention ouverte par l'utilisateur.
 */
class EtatTypeDerniereDemande
{
    /**
     * @var string Nom de la session qui contient la valeur du type de l'état de la dernière demande d'intervention
     */
    private static $ETAT_TYPE_SESSION_NOM = 'hopitalnumerique_intervention.etat_type_derniere_demande';
    /**
     * @var integer État d'une demande initiale
     */
    private static $ETAT_TYPE_DEMANDE_INITIALE = 1;
    /**
     * @var integer État d'une demande traitée par le CMSI
     */
    private static $ETAT_TYPE_DEMANDE_TRAITEE_CMSI = 2;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session Session
     */
    private $session;
    
    /**
     * Constructeur du service.
     * 
     * @param \Symfony\Component\HttpFoundation\Session\Session $session Session
     * @return void
     */
    public function __construct($session)
    {
        $this->session = $session;
    }

    /**
     * Indique la dernière demande d'intervention ouverte par l'utilisateur afin d'enregistrer son état en session.
     * 
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande
     * @return void
     */
    public function setDerniereDemandeOuverte(InterventionDemande $interventionDemande)
    {
        $etatType = null;

        if ($interventionDemande->interventionEtatEstDemandeInitiale() || $interventionDemande->interventionEtatEstAttenteCmsi())
            $etatType = self::$ETAT_TYPE_DEMANDE_INITIALE;
        else $etatType = self::$ETAT_TYPE_DEMANDE_TRAITEE_CMSI;

        $this->setEtatType($etatType);
    }
    /**
     * Indique le type d'état de la dernière demande et l'enregistre en session.
     * 
     * @param integer $etatTypeDerniereDemande Type d'état de la dernière demande
     */
    public function setEtatType($etatTypeDerniereDemande)
    {
        $this->enregistreEtatSession($etatTypeDerniereDemande);
    }
    /**
     * Retourne le type d'état pour Demande initiale.
     * 
     * @return integer Le type d'état pour Demande initiale
     */
    public function getEtatTypeDemandeInitiale()
    {
        return self::$ETAT_TYPE_DEMANDE_INITIALE;
    }
    /**
     * Retourne le type d'état pour Demande traitée par le CMSI.
     * 
     * @return integer Le type d'état pour Demande traitée par le CMSI
     */
    public function getEtatTypeDemandeTraiteeCmsi()
    {
        return self::$ETAT_TYPE_DEMANDE_TRAITEE_CMSI;
    }
    /**
     * Retourne si la dernière demande ouverte et de type Demande traitée par le CMSI.
     * 
     * @return boolean VRAI ssi la dernière demande ouverte et de type Demande traitée par le CMSI
     */
    public function derniereDemandeEstEtatTypeDemandeTraiteeCmsi()
    {
        return ($this->getEtatSession() == self::$ETAT_TYPE_DEMANDE_TRAITEE_CMSI);
    }
    
    
    /**
     * Enregistre en session un type d'état.
     * 
     * @param integer $etatType Type d'état
     * @return void
     */
    private function enregistreEtatSession($etatType)
    {
        $this->session->set(self::$ETAT_TYPE_SESSION_NOM, $etatType);
    }
    /**
     * Retourne le type d'état enregistré en session (Demande initiale par défaut).
     * 
     * @return integer $etatType Le type d'état en session
     */
    private function getEtatSession()
    {
        return $this->session->get(self::$ETAT_TYPE_SESSION_NOM, self::$ETAT_TYPE_DEMANDE_INITIALE);
    }
}