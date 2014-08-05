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
        foreach($glossaires as $one){
            $firstL = substr( ucfirst($one->getMot()), 0, 1);
            $datas[ $firstL ][ strtolower($one->getMot()) ] = $one;
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
        foreach($datas as $one)
            $glossairesWords[ trim(htmlentities($one->getMot())) ] = trim(htmlentities($one->getMot()));
        
        //tri des éléments les plus longs aux plus petits
        array_multisort(
            array_map(create_function('$v', 'return strlen($v);'), array_keys($glossairesWords)), SORT_DESC, 
            $glossairesWords
        );

        //objets
        if( count($objets) > 0){
            foreach($objets as $objet){
                $motsFounds = array();
                $posFounds  = array();

                //parse Resume + Synthese
                $words = strip_tags($objet->getResume()) . ' ' . strip_tags($objet->getSynthese());

                foreach($glossairesWords as $glossairesWord){
                    $pos = strpos($words, $glossairesWord);

                    if( $pos !== false && !in_array($pos, $posFounds) ){
                        $motsFounds[] = $glossairesWord;
                        $posFounds[]  = $pos;
                    }
                }

                if( count($motsFounds) > 0 )
                    $objet->setGlossaires( $motsFounds );
                else
                    $objet->setGlossaires( array() );
            }    
        }

        //contenus
        if( count($contenus) > 0){
            foreach($contenus as $contenu){
                $motsFounds = array();
                $posFounds  = array();

                //parse Contenu
                $words = strip_tags($contenu->getContenu());

                foreach($glossairesWords as $glossairesWord){
                    $pos = strpos($words, $glossairesWord);

                    if( $pos !== false && !in_array($pos, $posFounds) ){
                        $motsFounds[] = $glossairesWord;
                        $posFounds[]  = $pos;
                    }
                }

                if( count($motsFounds) > 0 )
                    $contenu->setGlossaires( $motsFounds );
                else
                    $contenu->setGlossaires( array() );
            }    
        }
    }
}