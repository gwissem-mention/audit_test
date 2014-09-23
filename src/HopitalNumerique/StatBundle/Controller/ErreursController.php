<?php

namespace HopitalNumerique\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ErreursController extends Controller
{
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

        $resultat = $this->getAllUrlObjets($dateDebutDateTime, $dateFinDateTime);
        
        return $this->render('HopitalNumeriqueStatBundle:Back:partials/Erreurs/tableau.html.twig', array(
            'urls'   => $resultat['urls'],
            'objets' => $resultat['objets']
        ));
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
            'URL'           => array()
        );

        $objets = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsByDate($dateDebut, $dateFin);
        $objetsArray = array();

        foreach ($objets as $key => $objet) 
        {
            $urls                         = $this->getUrlByObjet($objet, $urls);
            $objetsArray[$objet->getId()] = $objet;
        }

        return array(
            'urls'   =>$urls,
            'objets' => $objetsArray
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
        $res = array( $objet->getId() => array() );

        $urls = $this->recuperationLien($objet->getSynthese(), $objet->getId(), $urls);
        $urls = $this->recuperationLien($objet->getResume(), $objet->getId(), $urls);

        foreach ($objet->getContenus() as $key => $contenu) 
        {
            $urls = $this->recuperationLien($contenu->getContenu(), $objet->getId(), $urls);
        }
        return $urls;
    }

    /**
     * Récupération des liens internes des articles/objet/contenu..
     *
     * @param string $texte   Texte à vérifier
     * @param int    $idObjet Identifiant de l'objet courant, pour savoir d'où vient l'url
     * @param array  $urls    Tableau contenant les urls déjà trouvée
     *
     * @return array Tableau contenant les urls déjà trouvée
     */
    private function recuperationLien($texte, $idObjet, $urls)
    {
        //Récupération des urls complètes
        // The Regular Expression filter
        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

        // Check if there is a url in the text
        if(preg_match($reg_exUrl, $texte, $url)) 
        {   
            if(!array_key_exists($idObjet, $urls['URL']))
                $urls['URL'][$idObjet] = array();
            $urls['URL'][$idObjet][] = trim($url[0], '"');
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
                                $urls['PUBLICATION'][$idObjet] = array();
                            $urls['PUBLICATION'][$idObjet][$objet->getId()] = $this->getRequest()->getUriForPath('/publication/' . $matches[2][$key] . '-' . $objet->getAlias());
                        }
                        break;
                    case 'INFRADOC':
                        //cas contenu
                        $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array( 'id' => $matches[2][$key] ) );
                        if( $contenu )
                        {
                            if(!array_key_exists($idObjet, $urls['INFRADOC']))
                                $urls['INFRADOC'][$idObjet] = array();
                            $objet  = $contenu->getObjet();
                            $urls['INFRADOC'][$idObjet][$contenu->getId()] = $this->getRequest()->getUriForPath('/publication/'. $objet->getId().'-' . $objet->getAlias() . '/'.$matches[2][$key].'-'.$contenu->getAlias());
                        }
                        break;
                    case 'ARTICLE':
                        //cas Objet
                        $objet  = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'id' => $matches[2][$key] ) );
                        if($objet)
                        {
                            if(!array_key_exists($idObjet, $urls['ARTICLE']))
                                $urls['ARTICLE'][$idObjet] = array();
                            $urls['ARTICLE'][$idObjet][$objet->getId()] = $this->getRequest()->getUriForPath('/publication/article/'.$matches[2][$key].'-' . $objet->getAlias());
                        }
                        break;
                    case 'AUTODIAG':
                        //cas Outil
                        $outil  = $this->get('hopitalnumerique_autodiag.manager.outil')->findOneBy( array( 'id' => $matches[2][$key] ) );
                        if($outil)
                        {
                            if(!array_key_exists($idObjet, $urls['AUTODIAG']))
                                $urls['AUTODIAG'][$idObjet] = array();
                            $urls['AUTODIAG'][$idObjet][$outil->getId()] = $this->getRequest()->getUriForPath('/autodiagnostic/outil/'.$outil->getId() . '-' . $outil->getAlias());
                        } 
                        break;
                    case 'QUESTIONNAIRE':
                        //cas Questionnaire
                        $questionnaire  = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy( array( 'id' => $matches[2][$key] ) );
                        if($questionnaire)
                        {
                            if(!array_key_exists($idObjet, $urls['QUESTIONNAIRE']))
                                $urls['QUESTIONNAIRE'][$idObjet] = array();
                            $urls['QUESTIONNAIRE'][$idObjet][$questionnaire->getId()] = $this->getRequest()->getUriForPath('/questionnaire/edit/'. $questionnaire->getId());
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
