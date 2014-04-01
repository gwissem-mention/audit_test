<?php
/**
 * Classe affichant les logs des courriels qui ont été envoyés.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\DependencyInjection\Demande;

/**
 * Classe affichant les logs des courriels qui ont été envoyés.
 */
class EnvoiCourrielsAffichageLogs
{
    /**
     * @var \Symfony\Bridge\Monolog\Logger Logger de l'application
     */
    private $logger;
    
    /**
     * Constructeur du service.
     * 
     * @param \Symfony\Bridge\Monolog\Logger $logger Logger de l'application
     * @return void
     */
    public function __construct($logger)
    {
        $this->logger = $logger;
    }
    
    public function getHtml()
    {
        $courrielsLogs = $this->getLogsCourriels();
        
        echo '<h1>Envoi des courriels</h1>';
        if (count($courrielsLogs) > 0)
        {
            echo '<ul>';
            foreach ($courrielsLogs as $courrielLog)
                echo '<li>'.$courrielLog['message'].'</li>';
            echo '</ul>';
        }
        else echo '<p>Aucune courriel envoyé.</p>';
    }
    private function getLogsCourriels()
    {
        $courrielsLogs = array();
        $logs = $this->logger->getLogs();
        
        foreach ($logs as $log)
        {
            if ($log['priorityName'] == 'INFO' && strlen($log['message']) > 8 && substr($log['message'], 0, 8) == 'Courriel')
                $courrielsLogs[] = $log;
        }
        
        return $courrielsLogs;
    }
}