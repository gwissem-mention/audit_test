<?php
/**
 * Classe affichant les logs des courriels qui ont été envoyés.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\DependencyInjection\Demande;

use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Classe affichant les logs des courriels qui ont été envoyés.
 */
class EnvoiCourrielsAffichageLogs
{
    /**
     * @var string Nom de la session qui contient les logs d'envois de courriel
     */
    private static $LOG_SESSION_NOM = 'hn_envoicourriels';
    
    /**
     * @var \Symfony\Bridge\Monolog\Logger Logger de l'application
     */
    private $logger;
    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session Session
     */
    private $session;
    
    /**
     * Constructeur du service.
     * 
     * @param \Symfony\Bridge\Monolog\Logger $logger Logger de l'application
     * @param \Symfony\Component\HttpFoundation\Session\Session $session Session
     * @return void
     */
    public function __construct(Logger $logger, Session $session)
    {
        $this->logger = $logger;
        $this->session = $session;
        
        $this->session->set(self::$LOG_SESSION_NOM, array());
    }
    
    /**
     * Ajoute un log d'envoi de courriel.
     * 
     * @param string $message Message du log
     */
    public function addLog($message)
    {
        $this->logger->info($message);
        
        $logsExistants = $this->session->get(self::$LOG_SESSION_NOM);
        $logsExistants[] = $message;
        $this->session->set(self::$LOG_SESSION_NOM, $logsExistants);
    }
    /**
     * Retourne l'affichage des logs en HTML.
     * 
     * @return string Liste HTML des logs
     */
    public function getHtml()
    {
        $courrielsLogs = $this->getLogs();
        
        echo '<h1>Envoi des courriels</h1>';
        if (count($courrielsLogs) > 0)
        {
            echo '<ul>';
            foreach ($courrielsLogs as $courrielLog)
                echo '<li>'.$courrielLog.'</li>';
            echo '</ul>';
        }
        else echo '<p>Aucune courriel envoyé.</p>';
    }
    /**
     * Retourne la liste des logs qui concernent l'envoi des courriels.
     * 
     * @return array Logs de l'envoi des courriels
     */
    private function getLogs()
    {
        return $this->session->get(self::$LOG_SESSION_NOM);
    }
}