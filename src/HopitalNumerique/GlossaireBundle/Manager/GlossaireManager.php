<?php

namespace HopitalNumerique\GlossaireBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\UserBundle\Manager\UserManager;

use HopitalNumerique\GlossaireBundle\Entity\Glossaire;

/**
 * Manager de l'entité Glossaire.
 */
class GlossaireManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\GlossaireBundle\Entity\Glossaire';
    protected $_userManager;

    /**
     * Constructeur du manager gérant les références
     *
     * @param \Doctrine\ORM\EntityManager $entityManager EntityManager
     * @return void
     */
    public function __construct(EntityManager $entityManager, UserManager $userManager)
    {
        parent::__construct($entityManager);

        $this->_userManager = $userManager;
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( \StdClass $condition = null )
    {
        $glossairesForGrid = array();

        $domainesIds = $this->_userManager->getUserConnected()->getDomainesId();

        $glossaires = $this->getRepository()->getDatasForGrid( $domainesIds, $condition )->getQuery()->getResult();

        foreach ($glossaires as $glossaire) 
        {
            if(!array_key_exists($glossaire['id'], $glossairesForGrid))
            {
                $glossairesForGrid[$glossaire['id']] = $glossaire;
            }
            else
            {
                $glossairesForGrid[$glossaire['id']]['domaineNom'] .= ";" . $glossaire['domaineNom'];
            }
        }

        return array_values($glossairesForGrid);
    }

    /**
     * Récupère les données pour l'export CSV
     *
     * @return array
     */
    public function getDatasForExport( $ids )
    {
        $glossairesForExport = array();

        $glossaires = $this->getRepository()->getDatasForExport( $ids )->getQuery()->getResult();

        foreach ($glossaires as $glossaire) 
        {
            if(!array_key_exists($glossaire['id'], $glossairesForExport))
            {
                $glossairesForExport[$glossaire['id']] = $glossaire;
            }
            else
            {
                $glossairesForExport[$glossaire['id']]['domaineNom'] .= "|" . $glossaire['domaineNom'];
            }
        }

        return array_values($glossairesForExport);
    }

    /**
     * Retourne le tableau du glossaire
     *
     * @return array
     */
    public function findGlossaireTable()
    {
        $glossaires = $this->findAll();

        $datas = array();
        foreach($glossaires as $one)
        {
            if( $one->getEtat()->getId() == 3)
            {
                $firstL = substr( ucfirst($one->getMot()), 0, 1);
                $datas[ $firstL ][ strtolower($one->getMot()) ] = $one;
            }
        }

        foreach($datas as &$data)
        {
            ksort($data);
        }

        return $datas;
    }

    /**
     * Parse la liste des publications à la recherche de mots du glossaires
     *
     * @param array $objets Liste des objets
     * @param array $contenus Liste des contenus
     *
     * @return empty
     */
    public function parsePublications( $objets = array(), $contenus = array() )
    {
        //éléments du glossaire
        $datas = $this->findAll();
        $glossairesWords = array();
        foreach($datas as $one)
        {
            if( $one->getEtat()->getId() == 3)
            {
                $glossairesWords[ trim(htmlentities($one->getMot())) ] = array(
                    'sensitive' => $one->isSensitive(),
                    'domaines'  => $one->getDomainesId()
                );
            }
        }
        
        //tri des éléments les plus longs aux plus petits
        array_multisort(
            array_map(create_function('$v', 'return strlen($v);'), array_keys($glossairesWords)), SORT_DESC, 
            $glossairesWords
        );

        //objets
        if( count($objets) > 0){
            foreach($objets as $objet){
                //parse Resume + Synthese
                $words      = strip_tags($objet->getResume()) . ' ' . strip_tags($objet->getSynthese());
                $motsFounds = $this->searchWords( $words, $glossairesWords, $objet );

                $objet->setGlossaires( $motsFounds );
            }    
        }

        //contenus
        if( count($contenus) > 0){
            foreach($contenus as $contenu){
                //parse Contenu
                $words      = strip_tags($contenu->getContenu());
                $motsFounds = $this->searchWords( $words, $glossairesWords, $contenu->getObjet() );

                $contenu->setGlossaires( $motsFounds );
            }    
        }
    }

















    /**
     * [searchWords description]
     *
     * @param  [type] $words           [description]
     * @param  [type] $glossairesWords [description]
     *
     * @return [type]
     */
    private function searchWords( $words, $glossairesWords, $objet )
    {
        $motsFounds = array();
        $posFounds  = array();

        foreach($glossairesWords as $glossairesWord => $arrayGlossaire)
        {
            //Si le glossaire avait un domaine on vérifie qu'il correspond à celui de l'objet
            if(count($arrayGlossaire['domaines']) !== 0 )
            {
                $sameDomaine = false;
                foreach ($objet->getDomainesId() as $domaineObjetId) 
                {
                    if(in_array($domaineObjetId, $arrayGlossaire['domaines']))
                    {
                        $sameDomaine = true;
                        break;
                    }
                }

                if(!$sameDomaine)
                {
                    continue;
                }
            }

            $pattern = "|$glossairesWord|";
            if( !$arrayGlossaire['sensitive'] )
            {
                $pattern .= 'i';
            }

            preg_match_all($pattern, $words, $matches, PREG_OFFSET_CAPTURE);
            
            if( $matches[0] )
            {
                foreach($matches[0] as $match)
                {
                    if( !in_array($match[1], $posFounds) )
                    {
                        $motsFounds[] = $glossairesWord;
                        $posFounds[]  = $match[1];
                    }
                }
            }
        }

        return array_unique($motsFounds);
    }
}