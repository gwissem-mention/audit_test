<?php

namespace HopitalNumerique\PublicationBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CronController extends Controller
{
    /**
     * Cron de mise à jour des autodiag
     */
    public function generationAPCAction($id)
    {
        if ($id == 'THX3GNSYUUBW8D6TDAPG9Y79E7MC348RS5BFFZZHVJCJ4RQVQN')
        {
            ini_set("memory_limit","512M");
            ini_set('max_execution_time', 0);

            $objets = $this->get('hopitalnumerique_objet.manager.objet')->findBy(array('etat' => 3));

            foreach ($objets as $objet) 
            {
                $this->forward('HopitalNumeriquePublicationBundle:Publication:objet', array( 'id' => $objet->getId() ));

                $this->get('hopitalnumerique_publication.service.logger.cronlogger')->addLog('( Objet )' . $objet->getId() . " - " . $objet->getTitre());
            }

            $resultat .= "</ul><br />";

            $contenus = $this->get('hopitalnumerique_objet.manager.contenu')->findAll();

            $resultat .= "Cache créé pour les contenus suivants : <ul>";

            foreach ($contenus as $contenu) 
            {
                $this->forward('HopitalNumeriquePublicationBundle:Publication:contenu', array( 'id' => $contenu->getObjet()->getId(), 'idc' => $contenu->getId() ));

                $this->get('hopitalnumerique_publication.service.logger.cronlogger')->addLog('( Contenu ) ' . $contenu->getId() . " - " . $contenu->getTitre());
            }

            return new Response($this->get('hopitalnumerique_publication.service.logger.cronlogger')->getHtml()."<p>Fin du traitement : OK.</p>");
        }
        
        return new Response('Clef invalide.');
    }

    public function getLogsAction($id)
    {
        if ($id == 'THX3GNSYUUBW8D6TDAPG9Y79E7MC348RS5BFFZZHVJCJ4RQVQN')
        {
            return new Response($this->get('hopitalnumerique_publication.service.logger.cronlogger')->getHtml());
        }

        return new Response('Clef invalide.');
    }
}