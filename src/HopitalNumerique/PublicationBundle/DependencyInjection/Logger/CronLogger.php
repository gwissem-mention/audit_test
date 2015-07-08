<?php
/**
 * Classe affichant les logs du cron requetes
 */
namespace HopitalNumerique\PublicationBundle\DependencyInjection\Logger;

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
    private $sessionName = 'hn_crongenerateapc_publication';
    
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
        $pagesApced = $this->getLogs();
        
        echo '<h1>Cron generation apc sur publication</h1>';
        if (count($pagesApced) > 0)
        {
            echo '<ul>';
            foreach ($pagesApced as $pageApced)
                echo '<li>'.$pageApced.'</li>';
            echo '</ul>';
        }
        else echo '<p>Aucune page apc-ifiée.</p>';
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