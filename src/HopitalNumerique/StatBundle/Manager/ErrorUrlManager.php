<?php

namespace HopitalNumerique\StatBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité ErrorUrl.
 */
class ErrorUrlManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\StatBundle\Entity\ErrorUrl';

    /**
     * Retourne l'entité trouvée ou en créé une
     *
     * @param string $url URL testée
     *
     * @return array(StatRecherche)
     */
    public function existeErrorByUrl( $url )
    { 
        $errorUrl = is_null( $this->findOneBy(array('url' => $url)) ) ? $this->createEmpty() : $this->findOneBy(array('url' => $url));

        $errorUrl->setDateDernierCheck(new \DateTime());
        if(is_null($errorUrl->getId()) || $errorUrl->getId() == 0)
            $errorUrl->setUrl($url);

        return $errorUrl;
    }


    /**
     * Récupère les url pour l'export
     *
     * @return array
     */
    public function getDatasForExport( $donneesTab )
    {
        $results        = array();
        $errorsUrl      = $this->findAll();
        $errorsUrlByUrl = array();
        foreach ($errorsUrl as $errorUrl) 
        {
            $errorsUrlByUrl[$errorUrl->getUrl()] = $errorUrl;
        }

        //Boucle sur les différentes catégories (Publication, Infradoc, Article ...)
        foreach ($donneesTab['urls'] as $keyURL => $url) 
        {
            //Parmis les catégories, boucle sur les objets
            foreach ($url as $keyObjetUrl => $objetUrl) 
            {
                //Parcours l'ensemble des objets et contenu
                foreach ($objetUrl as $keyObjetOrContenu => $objetOrContenu) 
                {
                    //Parcours les urls de l'objet courants
                    foreach ($objetOrContenu as $urlObjetUrl) 
                    {
                        $row = array();

                        $row['type'] = ucfirst($keyURL);
                        $row['idObjet'] = $keyObjetUrl;
                        $row['titreObjet'] = $donneesTab['objets'][$keyObjetUrl]->getTitre();
                        //$row['infradoc'] = ($keyObjetOrContenu != 'objet') ?  (is_null($donneesTab['objets'][$keyObjetUrl]->getContenuById($keyObjetOrContenu)) ? '' : ( $donneesTab['objets'][$keyObjetUrl]->getContenuById($keyObjetOrContenu)->getOrder() . ' ' .  $donneesTab['objets'][$keyObjetUrl]->getContenuById($keyObjetOrContenu)->getTitre() ) : '';
                        $row['url'] = $urlObjetUrl;
                        $row['valide'] = (array_key_exists($urlObjetUrl, $errorsUrlByUrl)) ? ($errorsUrlByUrl[$urlObjetUrl]->getOk() ? 'Valide' : 'Non valide') : 'A vérifier';

                        if($keyObjetOrContenu != 'objet')
                        {
                            if(!is_null($donneesTab['objets'][$keyObjetUrl]->getContenuById($keyObjetOrContenu)))
                            {
                                $row['infradoc'] = $donneesTab['objets'][$keyObjetUrl]->getContenuById($keyObjetOrContenu)->getOrder() . ' ' .  $donneesTab['objets'][$keyObjetUrl]->getContenuById($keyObjetOrContenu)->getTitre();
                            }
                            else
                            {
                                $row['infradoc'] = '';
                            }
                        }
                        else
                        {
                            $row['infradoc'] = '';
                        }
                        
                        //add row To Results
                        $results[] = $row;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Récupère les url pour l'export des liens de l'autodiag
     *
     * @return array
     */
    public function getDatasForExportAutodiag( $donneesTab )
    {
        $results        = array();
        $errorsUrl      = $this->findAll();
        $errorsUrlByUrl = array();
        foreach ($errorsUrl as $errorUrl) 
        {
            $errorsUrlByUrl[$errorUrl->getUrl()] = $errorUrl;
        }

        //Boucle sur les différentes catégories
        foreach ($donneesTab['urls'] as $idChapitre => $arrayUrls) 
        {
            //Parmis les catégories, boucle sur les objets
            foreach ($arrayUrls as $typeUrl => $urls) 
            {
                //Parcours l'ensemble des objets et contenu
                foreach ($urls as $id => $url) 
                {
                    $row = array();

                    $row['id']       = $donneesTab['chapitres'][$idChapitre]->getOutil()->getId();
                    $row['titre']    = $donneesTab['chapitres'][$idChapitre]->getOutil()->getTitle();
                    $row['chapitre'] = $donneesTab['chapitres'][$idChapitre]->getTitle();
                    $row['question'] = "questions" ===  $typeUrl ? $donneesTab['chapitres'][$idChapitre]->getQuestionsById()[$id]->getTexte() : '-';
                    $row['url']      = $url;
                    $row['valide']   = (array_key_exists($url, $errorsUrlByUrl)) ? ($errorsUrlByUrl[$url]->getOk() ? 'Valide' : 'Non valide') : 'A vérifier';
                    
                    //add row To Results
                    $results[] = $row;
                   
                }
            }
        }

        return $results;
    }

    /**
     * Récupère l'état de l'url
     *
     * @return array
     */
    public function getOksByUrl()
    {
        $errorUrls = $this->getRepository()->findAll();

        $oks = array();

        foreach ($errorUrls as $errorUrl)
        {
            $oks[$errorUrl->getUrl()] = $errorUrl->getOk();
        }

        return $oks;

    }

    /**
     * Retourne toutes les erreurs par URL.
     *
     * @return array<string, \HopitalNumerique\StatBundle\Entity\ErrorUrl> Erreurs
     */
    public function findAllGroupedByUrl()
    {
        $erreurUrlsByUrl = [];

        foreach ($this->findAll() as $erreurUrl) {
            $erreurUrlsByUrl[$erreurUrl->getUrl()] = $erreurUrl;
        }

        return $erreurUrlsByUrl;
    }
}