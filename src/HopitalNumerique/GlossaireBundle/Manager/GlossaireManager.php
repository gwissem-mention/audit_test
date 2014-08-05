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
     *
     * @return empty
     */
    public function parsePublications( $objets )
    {
        //éléments du glossaire
        $datas = $this->findAll();
        $glossaires = array();
        foreach($datas as $one)
            $glossaires[ $one->getMot() ] = $one;
        
        $keys = array_keys($glossaires);

        //objets
        foreach($objets as $objet){
            $motsFounds = array();
            //parse Resume + Synthese
            $words = array_merge( explode( ' ', strip_tags($objet->getResume()) ), explode( ' ', strip_tags($objet->getSynthese()) ) );

            foreach($words as $word){
                if( in_array($word, $keys) ) 
                    $motsFounds[] = $word;
            }

            //on dédoublonne les mots trouvés
            $motsFounds = array_unique($motsFounds);

            if( count($motsFounds) > 0 )
                $objet->setGlossaires( $motsFounds );
            else
                $objet->setGlossaires( array() );
        }
    }
}