<?php

namespace HopitalNumerique\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ErreursController extends Controller
{
    /**
     * Cron de check des urls des objets
     */
    public function cronAction($id)
    {
        if ($id == 'SPTR6D7U5QFH4YMH5VVAXEWMTJ4XPCQBKGJR92E3')
        {
            set_time_limit(36000);
            $resultats = $this->getAllUrlObjets(null,null);
            $domaines = $this->get('hopitalnumerique_domaine.manager.domaine')->findBy(array(), array('nom' => 'ASC'));

            foreach ($domaines as $domaineExistant) {
                foreach ($resultats['urls'] as $categorieNom => $categsUrl) {
                    //Chaque catégorie des url (Publication, Infradoc, Article ...)
                    foreach ($categsUrl as $objetId => $urls) {
                        foreach ($urls as $keyObjetOrContenu => $objetOrContenu) {
                            //Parcourt du tableau des url des categs
                            foreach ($objetOrContenu as $url) {
                                $objet  = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'id' => $objetId ) );

                                if ($objet != null) {
                                    $domainesObjet = $objet->getDomaines();

                                    foreach ($domainesObjet as $domaineObjet) {

                                        if ($domaineObjet->getId() === $domaineExistant->getId()) {

                                            if (strpos($url, 'http') === false &&
                                                strpos($url, 'www.') === false){
                                                $url = $domaineObjet->getUrl() . $url;
                                            }

                                            //Set du booléan pour l'entité
                                            $isOk = true;

                                            $handle = curl_init(str_replace(' ', '%20', $url));
                                            curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);

                                            /* Get the HTML or whatever is linked in $url. */
                                            $response = curl_exec($handle);

                                            /* Check for not 200 */
                                            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
                                            if ($httpCode >= 400 || $httpCode === 0) {
                                                $isOk = false;
                                            }

                                            $this->get('hopitalnumerique_forum.service.logger.cronlogger')->addLog('Url ' . $url . ($isOk ? ' valide' : ' non valide.').' - code '.$httpCode);

                                            curl_close($handle);

                                            //Recherche si une entité existe déjà pour cette url
                                            $errorUrl = $this->get('hopitalnumerique_stat.manager.errorurl')->existeErrorByUrl($url);
                                            $errorUrl->setOk($isOk);
                                            $errorUrl->setCode($httpCode);

                                            $this->get('hopitalnumerique_stat.manager.errorurl')->save($errorUrl);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }


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
        return $this->render('HopitalNumeriqueStatBundle:Back:partials/Erreurs/bloc.html.twig', array());
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
        $dateDebut    = $request->request->get('datedebut-erreursCurl');
        $dateFin      = $request->request->get('dateFin-erreursCurl');

        //Récupération des dates sous forme DateTime
        $dateDebutDateTime = $dateDebut === "" ? null : new \DateTime($dateDebut);
        $dateFinDateTime   = $dateFin   === "" ? null : new \DateTime($dateFin);

        $resultats = $this->getAllUrlObjets($dateDebutDateTime, $dateFinDateTime);

        $domaines = $this->get('hopitalnumerique_domaine.manager.domaine')->findBy(array(), array('nom' => 'ASC'));

        return $this->render('HopitalNumeriqueStatBundle:Back:partials/Erreurs/tableau.html.twig', array(
            'urls'   => $resultats['urls'],
            'objets' => $resultats['objets'],
            'domaines' => $domaines,
            'oksByUrl'      => $resultats['oksByUrl']
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
        $dateDebut    = $request->request->get('datedebut-erreursCurl');
        $dateFin      = $request->request->get('dateFin-erreursCurl');

        //Récupération des dates sous forme DateTime
        $dateDebutDateTime = $dateDebut === "" ? null : new \DateTime($dateDebut);
        $dateFinDateTime   = $dateFin   === "" ? null : new \DateTime($dateFin);

        $resultat = $this->getAllUrlObjets($dateDebutDateTime, $dateFinDateTime);

        //Colonnes communes
        $colonnes = array(
            'type'       => 'Type du lien',
            'idObjet'    => 'Id de la publication',
            'titreObjet' => 'Titre de la publication',
            'infradoc'   => 'Infradoc ?',
            'url'        => 'Url',
            'valide'     => 'Valide',
        );

        $kernelCharset = $this->container->getParameter('kernel.charset');
        $datas         = $this->get('hopitalnumerique_stat.manager.errorurl')->getDatasForExport( $resultat );

        return $this->get('hopitalnumerique_stat.manager.errorurl')->exportCsv( $colonnes, $datas, 'export-erreurs-url.csv', $kernelCharset );
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

        $handle = curl_init(str_replace(' ', '%20', $url));
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
            $handle = curl_init(str_replace(' ', '%20', $url));
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
    private function getAllUrlObjets($dateDebut, $dateFin)
    {
        $urls = array(
            'PUBLICATION'   => array(),
            'INFRADOC'      => array(),
            'ARTICLE'       => array(),
            'AUTODIAG'      => array(),
            'QUESTIONNAIRE' => array(),
            'URL'           => array(),
            'FICHIER'       => array()
        );

        $objets = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsByDate($dateDebut, $dateFin);
        $objetsArray = array();

        foreach ($objets as $key => $objet)
        {
            $urls                         = $this->getUrlByObjet($objet, $urls);
            $objetsArray[$objet->getId()] = $objet;
        }

        return array(
            'urls'   => $urls,
            'objets' => $objetsArray,
            'oksByUrl' => $this->get('hopitalnumerique_stat.manager.errorurl')->getOksByUrl()
        );
    }

    /**
     * Pour un objet, parse les différents textes de ce dernier pour trouver les urls
     *
     * @param HopitalNumeriqueObjetBundleEntityObjet $objet Objet à parser
     * @param array                                  $urls  Tableau contenant les urls déjà trouvée
     *
     * @return array Tableau contenant les urls déjà trouvée
     */
    private function getUrlByObjet( \HopitalNumerique\ObjetBundle\Entity\Objet $objet, $urls )
    {
        if (null !== $objet->getPath()) {
            foreach ($objet->getDomaines() as $domaine) {
                $url = $domaine->getUrl().'/'.$objet->getWebPath(1);
                $urls['FICHIER'][$objet->getId()]['objet'][] = $url;
            }
        }
        if (null !== $objet->getPath2()) {
            foreach ($objet->getDomaines() as $domaine) {
                $url = $domaine->getUrl().'/'.$objet->getWebPath(2);
                $urls['FICHIER'][$objet->getId()]['objet'][] = $url;
            }
        }

        $res = array( $objet->getId() => array() );

        $urls = $this->recuperationLien($objet->getSynthese(), $objet->getId(), $urls);
        $urls = $this->recuperationLien($objet->getResume(), $objet->getId(), $urls);

        foreach ($objet->getContenus() as $key => $contenu)
        {
            $urls = $this->recuperationLien($contenu->getContenu(), $objet->getId(), $urls, true, $contenu->getId());
        }
        return $urls;
    }

    /**
     * Récupération des liens internes des articles/objet/contenu..
     *
     * @param string    $texte      Texte à vérifier
     * @param int       $idObjet    Identifiant de l'objet courant, pour savoir d'où vient l'url
     * @param array     $urls       Tableau contenant les urls déjà trouvée
     * @param boolean   $isContenu  Est un lien venant d'un contenu
     * @param int       $idContenu  Identifiant du contenu
     *
     * @return array Tableau contenant les urls déjà trouvée
     */
    private function recuperationLien($texte, $idObjet, $urls, $isContenu = false , $idContenu = 0)
    {
        //Récupération des urls complètes
        // The Regular Expression filter
        $reg_exUrl = "/\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))/";

        // Check if there is a url in the text
        preg_match_all($reg_exUrl, $texte, $matchesURLTemp);
        if(count($matchesURLTemp[0]) > 0 )
        {
            $matchesURL = $matchesURLTemp[0];
            foreach ($matchesURL as $matcheURL)
            {
                if(!array_key_exists($idObjet, $urls['URL']))
                {
                    $urls['URL'][$idObjet] = array(
                        'objet'    => array(),
                        $idContenu => array()
                    );
                }
                if($isContenu)
                    $urls['URL'][$idObjet][$idContenu][] = trim($matcheURL, '"');
                else
                    $urls['URL'][$idObjet]['objet'][] = trim($matcheURL, '"');
            }
        }

        //Remplacement des liens internes
        $pattern = '/\[([a-zA-Z]+)\:(\d+)\;(([a-zA-Z0-9àáâãäåçèéêëìíîïðòóôõöùúûüýÿ\&\'\`\"\<\>\!\:\?\,\;\.\%\#\@\_\-\+]| )*)\;([a-zA-Z0-9]+)\]/';
        preg_match_all($pattern, $texte, $matches);

        // matches[0] tableau des chaines completes trouvée
        // matches[1] tableau des chaines avant les : trouvé
        // matches[2] tableau des ID après les : trouvé
        if(is_array($matches[1]))
        {
            foreach($matches[1] as $key => $value)
            {
                switch($value){
                    case 'PUBLICATION':
                        //cas Objet
                        $objet  = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'id' => $matches[2][$key] ) );
                        if($objet)
                        {
                            if(!array_key_exists($idObjet, $urls['PUBLICATION']))
                            {
                                //Tableau
                                $urls['PUBLICATION'][$idObjet] = array(
                                    'objet'   => array(),
                                    $idContenu => array()
                                );
                            }
                            if($isContenu)
                                $urls['PUBLICATION'][$idObjet][$idContenu][$objet->getId()] = '/publication/' . $matches[2][$key] . '-' . $objet->getAlias();
                            else
                                $urls['PUBLICATION'][$idObjet]['objet'][$objet->getId()] = '/publication/' . $matches[2][$key] . '-' . $objet->getAlias();
                        }
                        break;
                    case 'INFRADOC':
                        //cas contenu
                        $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array( 'id' => $matches[2][$key] ) );
                        if( $contenu )
                        {
                            if(!array_key_exists($idObjet, $urls['INFRADOC']))
                            {
                                $urls['INFRADOC'][$idObjet] = array(
                                    'objet'   => array(),
                                    $idContenu => array()
                                );
                            }
                            $objet  = $contenu->getObjet();
                            if($isContenu)
                                $urls['INFRADOC'][$idObjet][$idContenu][$contenu->getId()] = '/publication/'. $objet->getId().'-' . $objet->getAlias() . '/'.$matches[2][$key].'-'.$contenu->getAlias();
                            else
                                $urls['INFRADOC'][$idObjet]['objet'][$contenu->getId()] = '/publication/'. $objet->getId().'-' . $objet->getAlias() . '/'.$matches[2][$key].'-'.$contenu->getAlias();
                        }
                        break;
                    case 'ARTICLE':
                        //cas Objet
                        $objet  = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'id' => $matches[2][$key] ) );
                        if($objet)
                        {
                            if(!array_key_exists($idObjet, $urls['ARTICLE']))
                            {
                                $urls['ARTICLE'][$idObjet] = array(
                                    'objet'   => array(),
                                    $idContenu => array()
                                );
                            }
                            if($isContenu)
                                $urls['ARTICLE'][$idObjet][$idContenu][$objet->getId()] = '/publication/article/'.$matches[2][$key].'-' . $objet->getAlias();
                            else
                                $urls['ARTICLE'][$idObjet]['objet'][$objet->getId()] = '/publication/article/'.$matches[2][$key].'-' . $objet->getAlias();
                        }
                        break;
                    case 'AUTODIAG':
                        //cas Outil
                        $outil  = $this->get('hopitalnumerique_autodiag.manager.outil')->findOneBy( array( 'id' => $matches[2][$key] ) );
                        if($outil)
                        {
                            if(!array_key_exists($idObjet, $urls['AUTODIAG']))
                            {
                                $urls['AUTODIAG'][$idObjet] = array(
                                    'objet'   => array(),
                                    $idContenu => array()
                                );
                            }
                            if($isContenu)
                                $urls['AUTODIAG'][$idObjet][$idContenu][$outil->getId()] = '/autodiagnostic/outil/'.$outil->getId() . '-' . $outil->getAlias();
                            else
                                $urls['AUTODIAG'][$idObjet]['objet'][$outil->getId()] = '/autodiagnostic/outil/'.$outil->getId() . '-' . $outil->getAlias();
                        }
                        break;
                    case 'QUESTIONNAIRE':
                        //cas Questionnaire
                        $questionnaire  = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy( array( 'id' => $matches[2][$key] ) );
                        if($questionnaire)
                        {
                            if(!array_key_exists($idObjet, $urls['QUESTIONNAIRE']))
                            {
                                $urls['QUESTIONNAIRE'][$idObjet] = array(
                                    'objet'   => array(),
                                    $idContenu => array()
                                );
                            }
                            if($isContenu)
                                $urls['QUESTIONNAIRE'][$idObjet][$idContenu][$questionnaire->getId()] = '/questionnaire/edit/'. $questionnaire->getId();
                            else
                                $urls['QUESTIONNAIRE'][$idObjet]['objet'][$questionnaire->getId()] = '/questionnaire/edit/'. $questionnaire->getId();
                        }
                        break;
                }
            }
        }

        return $urls;
    }

    /**
     * to assci
     */
    private function toascii($string)
    {
        if(!empty($string)){
            $tempo = utf8_decode($string);
            $string = '';
            foreach (str_split($tempo) as $obj)
            {
                $string .= '&#' . ord($obj) . ';';
            }
        }
        return $string;
    }
}
