<?php
namespace HopitalNumerique\ModuleBundle\Controller\Cron;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CronController extends Controller
{
    /**
     * Cron de mise à jour des requetes
     */
    public function envoieMailsRappelAction($id)
    {
        if ($id == 'BHFYRJYIHOLPMFFVIDUEKQGEUJRCQSFA')
        {
            //Récupérations de tout les modules
            $modules = $this->get('hopitalnumerique_module.manager.module')->getAllInscriptionsBySessionsActivesNonPasseesByModules();
            
            //Récupération des dates à date d'aujourd'hui + 7 jours
            $today               = new \DateTime();
            $today->setTime(0,0,0);
            $dateIntervalOneWeek = new \DateInterval('P7D');
            $oneWeek             = $today->add( $dateIntervalOneWeek );

            foreach ($modules as $module) 
            {
                //Récupérations des sessions actives
                $sessions = $module->getSessionsActives();

                foreach ($sessions as $session)
                {
                    if($session->getDateSession() == $oneWeek)
                    {
                        //récupérations des inscriptions acceptées≤
                        $inscriptions = $session->getInscriptionsAccepte();

                        //Envoyer mail de refus de l'inscription
                        $mails = $this->get('nodevo_mail.manager.mail')->sendRappelInscriptionMail($inscriptions,array());
                        foreach ($mails as $mail)
                        {
                            $this->get('mailer')->send($mail);
                        }
                        //Ajout dans le log
                        foreach ($inscriptions as $inscription) 
                        {
                            $this->get('hopitalnumerique_module.service.logger.cronlogger')->addLog('mail send to : ' . $inscription->getUser()->getEmail() );
                        }
                    }
                }
            }

            return new Response($this->get('hopitalnumerique_module.service.logger.cronlogger')->getHtml().'<p>Fin du traitement : OK.</p>');
        }
        
        return new Response('Clef invalide.');
    }
}