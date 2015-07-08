<?php

namespace HopitalNumerique\PublicationBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\Common\Cache\ApcCache;

class CronController extends Controller
{
    /**
     * Cron de mise à jour des autodiag
     */
    public function generationAPCAction($id)
    {
        if ($id == 'THX3GNSYUUBW8D6TDAPG9Y79E7MC348RS5BFFZZHVJCJ4RQVQN')
        {
            // ini_set("memory_limit","512M");
            // ini_set('max_execution_time', 0);
            
            $cacheDriver = new ApcCache();
            $objets = $this->get('hopitalnumerique_objet.manager.objet')->findBy(array('etat' => 3));

            $resultat = "Cache créé pour les objets suivants : <ul>";

            foreach ($objets as $objet) 
            {
                //Destruction du cache APC concernant la page à regenerer
                $cacheName = "_publication_objet_" . $objet->getId();
                $cacheDriver->delete($cacheName);

                $url = $this->generateUrl('hopital_numerique_publication_publication_objet', array( 'id' => $objet->getId() ), true );

                $handle = curl_init($url);
                curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
                $response = curl_exec($handle);

                $resultat .= "<li>" . $objet->getId() . " - " . $objet->getTitre() . "</li>";

                curl_close($handle);
            }

            $resultat .= "</ul><br />";

            $contenus = $this->get('hopitalnumerique_objet.manager.contenu')->findAll();

            $resultat .= "Cache créé pour les contenus suivants : <ul>";

            foreach ($contenus as $contenu) 
            {
                //Destruction du cache APC concernant la page à regenerer
                $cacheName = "_publication_contenu_" . $contenu->getId();
                $cacheDriver->delete($cacheName);

                $url = $this->generateUrl('hopital_numerique_publication_publication_contenu', array( 'id' => $contenu->getObjet()->getId(), 'idc' => $contenu->getId() ), true );

                $handle = curl_init($url);
                curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
                $response = curl_exec($handle);

                $resultat .= "<li>" . $contenu->getId() . " - " . $contenu->getTitre() . "</li>";

                curl_close($handle);
            }

            $resultat .= "</ul><br />";
            $resultat .= "<p>Fin du traitement : OK.</p>";

            return new Response($resultat);
        }
        
        return new Response('Clef invalide.');
    }
}