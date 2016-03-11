<?php

namespace HopitalNumerique\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ErreursAutodiagController extends Controller
{
    /**
     * @var array<\HopitalNumerique\StatBundle\Entity\ErrorUrl> Liste de toutes les erreurs d'URL
     */
    private $erreurUrlsGroupedByUrl = null;


    /**
     * Cron de check des urls des objets
     */
    public function cronAction($id)
    {
    	$compteur = 0;
        if ($id == 'EAHXP3S72C4NNDZBVJF8WJ9FT9G5UACR2RQHWEUSKXMBGQY6Z7')
        {
            $resultats = $this->getAllUrlOutils(null,null);

            foreach ($resultats['urls'] as $categsUrl) 
            {
                foreach ($categsUrl as $typeCateg => $arrayUrl) 
                {
                    foreach ($arrayUrl as $idQuestion => $urlCaracteristiques) 
                    {
                        //Set du booléan pour l'entité
                        $isOk = true;

                        $handle = curl_init($urlCaracteristiques['url']);
                        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

                        /* Get the HTML or whatever is linked in $url. */
                        $response = curl_exec($handle);

                        /* Check for not 200 */
                        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
                        if($httpCode >= 400 || $httpCode === 0) 
                        {
                            $isOk = false;
                        }

                        $this->get('hopitalnumerique_forum.service.logger.cronlogger')->addLog('Url ' . $urlCaracteristiques['url'] . ($isOk ? ' valide' : ' non valide.'));

                        curl_close($handle);

                        //Recherche si une entité existe déjà pour cette url
                        $errorUrl = $this->get('hopitalnumerique_stat.manager.errorurl')->existeErrorByUrl($urlCaracteristiques['url']);
                        $errorUrl->setOk($isOk);
                        $errorUrl->setCode($httpCode);

                        $this->get('hopitalnumerique_stat.manager.errorurl')->save($errorUrl);

                        $compteur++;
                    }
                }
            }

            $this->get('hopitalnumerique_forum.service.logger.cronlogger')->addLog('Nombre d\'url(s) trouvé(s): ' . $compteur);

            return new Response($this->get('hopitalnumerique_forum.service.logger.cronlogger')->getHtml().'<p>Fin du traitement : OK.</p>');
        }
        
        return new Response('Clef invalide.');
    }

    /**
     * Affiche les statistiques des items de requete
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo™
     */
    public function indexAction( )
    {
        return $this->render('HopitalNumeriqueStatBundle:Back:partials/Erreurs_autodiag/bloc.html.twig', array());
    }

    /**
     * Génération du tableau à exporter
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * 
     * @return View
     */
    public function generateTableauAction( Request $request )
    {
        //Récupération de la requete
        $dateDebut    = $request->request->get('datedebut-erreursCurlAutodiag');
        $dateFin      = $request->request->get('dateFin-erreursCurlAutodiag');

        //Récupération des dates sous forme DateTime
        $dateDebutDateTime = $dateDebut === "" ? null : new \DateTime($dateDebut);
        $dateFinDateTime   = $dateFin   === "" ? null : new \DateTime($dateFin);

        $resultat = $this->getAllUrlOutils($dateDebutDateTime, $dateFinDateTime);
        $chapitres = $this->get('hopitalnumerique_autodiag.manager.chapitre')->getChapitresById();

        return $this->render('HopitalNumeriqueStatBundle:Back:partials/Erreurs_autodiag/tableau.html.twig', array(
            'urls'         => $resultat['urls'],
            'chapitres'    => $resultat['chapitres'],
            'allChapitres' => $chapitres
        ));
    }

    /**
     * Génération du tableau à exporter
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * 
     * @return View
     */
    public function exportCSVAction( Request $request )
    {
        //Récupération de la requete
        $dateDebut    = $request->request->get('datedebut-erreursCurlAutodiag');
        $dateFin      = $request->request->get('dateFin-erreursCurlAutodiag');

        //Récupération des dates sous forme DateTime
        $dateDebutDateTime = $dateDebut === "" ? null : new \DateTime($dateDebut);
        $dateFinDateTime   = $dateFin   === "" ? null : new \DateTime($dateFin);

        $resultat = $this->getAllUrlOutils($dateDebutDateTime, $dateFinDateTime);

        //Colonnes communes
        $colonnes = array(
            'id'       => 'Id',
            'titre'    => 'Titre',
            'chapitre' => 'Chapitre',
            'question' => 'Question',
            'url'      => 'URL',
            'valide'   => 'Valide',
        );

        $kernelCharset = $this->container->getParameter('kernel.charset');
        $datas         = $this->get('hopitalnumerique_stat.manager.errorurl')->getDatasForExportAutodiag( $resultat );

        return $this->get('hopitalnumerique_stat.manager.errorurl')->exportCsv( $colonnes, $datas, 'export-erreurs-url-autodiag.csv', $kernelCharset );
    }

    /**
    * Génération du tableau à exporter
    *
    * @param  Symfony\Component\HttpFoundation\Request  $request
    * 
    * @return View
    */
    public function curlAction( Request $request )
    {
        //Récupération de la requete
        $url    = $request->request->get('url');

        $handle = curl_init($url);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);

        /* Check for not 200 */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if($httpCode >= 400 || $httpCode === 0) 
        {
            curl_close($handle);
            return new Response('{"success":false}', 200);
        }

        curl_close($handle);
        return new Response('{"success":true}', 200);
    }

    /**
    * Génération du tableau à exporter
    *
    * @param  Symfony\Component\HttpFoundation\Request  $request
    * 
    * @return View
    */
    public function curlWithBaseAction( Request $request )
    {
        //Récupération de la requete
        $url    = $request->request->get('url');

        $errorUrl = $this->get('hopitalnumerique_stat.manager.errorurl')->findOneBy(array('url' => $url));

        if(is_null($errorUrl))
        {
            $handle = curl_init($url);
            curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

            /* Get the HTML or whatever is linked in $url. */
            $response = curl_exec($handle);

            /* Check for not 200 */
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            if($httpCode >= 400 || $httpCode === 0) 
            {
                curl_close($handle);
                return new Response('{"success":false}', 200);
            }

            curl_close($handle);
            return new Response('{"success":true}', 200);
        }
        else
        {
            if($errorUrl->getOk())
            {
                return new Response('{"success":true}', 200);
            }
            else
            {
                return new Response('{"success":false}', 200);
            }
        }
    }






    /**
     * Récupère l'ensemble des url de tout les objets présent en base
     *
     * @return array(string) Array = clé:Id de l'objet, value: Url
     */
    private function getAllUrlOutils($dateDebut, $dateFin)
    {
        $urls = array();

        $autodiags = $this->get('hopitalnumerique_autodiag.manager.outil')->getOutilsByDate($dateDebut, $dateFin);
        $chapitresArray = array();

        foreach ($autodiags as $autodiag) 
        {
            foreach ($autodiag->getChapitres() as $chapitre) 
            {
                $urls = $this->getUrlByChapitre($chapitre, $urls);
                $chapitresArray[$chapitre->getId()] = $chapitre;
            }
        }

        return array(
            'urls'      => $urls,
            'chapitres' => $chapitresArray
        );
    }

    /**
     * Pour un chapitre cherche les urls de ses questions et de ce dernier
     *
     * @param HopitalNumerique\AutodiagBundle\Entity\Chapitre $chapitre Chapitre à parser
     * @param array                                  $urls  Tableau contenant les urls déjà trouvée
     *
     * @return array Tableau contenant les urls déjà trouvée
     */
    private function getUrlByChapitre( \HopitalNumerique\AutodiagBundle\Entity\Chapitre $chapitre, $urls )
    {
        //Récupération des urls complètes
        // The Regular Expression filter
        $reg_exUrl = "/\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))/";

        // Check if there is a url in the text
        //Sur le champ "LIEN"
        preg_match_all($reg_exUrl, $chapitre->getLien(), $matchesURLTemp);
        if(count($matchesURLTemp[0]) > 0 )
        {
            $matchesURL = $matchesURLTemp[0];
            foreach ($matchesURL as $matcheURL)
            {
                if(!array_key_exists($chapitre->getId(), $urls))
                {
                    $urls[$chapitre->getId()] = array('chapitre' => array());
                }
                $url = trim($matcheURL, '"');
                $urls[$chapitre->getId()]['chapitre'][] = [
                    'url' => $url,
                    'ok' => $this->urlIsOk($url)
                ];
            }
        }
        //Sur le champ "description"
        preg_match_all($reg_exUrl, $chapitre->getLien(), $matchesURLTemp);
        if(count($matchesURLTemp[0]) > 0 )
        {
            $matchesURL = $matchesURLTemp[0];
            foreach ($matchesURL as $matcheURL) 
            {
                if(!array_key_exists($chapitre->getId(), $urls))
                {
                    $urls[$chapitre->getId()] = array();
                }

                $url = trim($matcheURL, '"');
                $urls[$chapitre->getId()]['chapitre'][] = [
                    'url' => $url,
                    'ok' => $this->urlIsOk($url)
                ];
            }
        }
        foreach ($chapitre->getQuestions() as $question) 
        {
            preg_match_all($reg_exUrl, $question->getLien(), $matchesURLTemp);
            if(count($matchesURLTemp[0]) > 0 )
            {
                $matchesURL = $matchesURLTemp[0];
                foreach ($matchesURL as $matcheURL) 
                {
                    if(!array_key_exists($chapitre->getId(), $urls))
                    {
                        $urls[$chapitre->getId()] = array('questions' => array());
                    }

                    $url = trim($matcheURL, '"');
                    $urls[$chapitre->getId()]['questions'][$question->getId()] = [
                        'url' => $url,
                        'ok' => $this->urlIsOk($url)
                    ];
                }
            }
        }

        return $urls;
    }

    /**
     * Initialise les erreurs d'URL.
     */
    private function initErreurUrls()
    {
        if (null === $this->erreurUrlsGroupedByUrl) {
            $this->erreurUrlsGroupedByUrl = $this->container->get('hopitalnumerique_stat.manager.errorurl')->findAllGroupedByUrl();
        }
    }

    /**
     * Retourne si l'URL est OK.
     *
     * @param string $url URL
     * @return bool|null Ok
     */
    private function urlIsOk($url)
    {
        $this->initErreurUrls();
        if (array_key_exists($url, $this->erreurUrlsGroupedByUrl)) {
            return $this->erreurUrlsGroupedByUrl[$url]->getOk();
        }

        return null;
    }
}
