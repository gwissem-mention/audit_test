<?php
/**
 * Classe affichant les logs du cron requetes
 */
namespace HopitalNumerique\RechercheBundle\DependencyInjection\Logger;

use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Classe affichant les logs du cron requete
 */
class CronLogger
{
    /**
     * @var string Nom de la session qui contient les logs d'envois de courriel
     */
    private $sessionName = 'hn_cronrequte';
    
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
        $this->logger  = $logger;
        $this->session = $session;
        
        $this->session->set($this->sessionName, array());
    }
    
    /**
     * Ajoute un log d'envoi de courriel.
     * 
     * @param string $message Message du log
     */
    public function addLog($message)
    {
        $this->logger->info($message);
        
        $logsExistants   = $this->session->get($this->sessionName);
        $logsExistants[] = $message;
        $this->session->set($this->sessionName, $logsExistants);
    }

    /**
     * Retourne l'affichage des logs en HTML.
     * 
     * @return string Liste HTML des logs
     */
    public function getHtml()
    {
        $courrielsLogs = $this->getLogs();
        
        echo '<h1>Cron requete</h1>';
        if (count($courrielsLogs) > 0)
        {
            echo '<ul>';
            foreach ($courrielsLogs as $courrielLog)
                echo '<li>'.$courrielLog.'</li>';
            echo '</ul>';
        }
        else echo '<p>Aucune mail envoyé.</p>';
    }
    
    /**
     * Retourne la liste des logs qui concernent l'envoi des courriels.
     * 
     * @return array Logs de l'envoi des courriels
     */
    private function getLogs()
    {
        return $this->session->get($this->sessionName);
    }
}