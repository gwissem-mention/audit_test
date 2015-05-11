<?php

namespace HopitalNumerique\GlossaireBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Glossaire.
 */
class GlossaireManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\GlossaireBundle\Entity\Glossaire';

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
            ksort($data);

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
        foreach($datas as $one){
            if( $one->getEtat()->getId() == 3)
                $glossairesWords[ trim(htmlentities($one->getMot())) ] = $one->isSensitive();
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
                $motsFounds = $this->searchWords( $words, $glossairesWords );

                $objet->setGlossaires( $motsFounds );
            }    
        }

        //contenus
        if( count($contenus) > 0){
            foreach($contenus as $contenu){
                //parse Contenu
                $words      = strip_tags($contenu->getContenu());
                $motsFounds = $this->searchWords( $words, $glossairesWords );

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
    private function searchWords( $words, $glossairesWords )
    {
        $motsFounds = array();
        $posFounds  = array();

        foreach($glossairesWords as $glossairesWord => $sensitive){
            $pattern = "|$glossairesWord|";
            if( !$sensitive )
                $pattern .= 'i';

            preg_match_all($pattern, $words, $matches, PREG_OFFSET_CAPTURE);
            
            if( $matches[0] ){
                foreach($matches[0] as $match){
                    if( !in_array($match[1], $posFounds) ){
                        $motsFounds[] = $glossairesWord;
                        $posFounds[]  = $match[1];
                    }
                }
            }
        }

        return array_unique($motsFounds);
    }
}