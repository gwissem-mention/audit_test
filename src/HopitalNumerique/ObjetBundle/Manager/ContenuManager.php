<?php

namespace HopitalNumerique\ObjetBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

use \Nodevo\ToolsBundle\Tools\Chaine;

/**
 * Manager de l'entité Contenu.
 */
class ContenuManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ObjetBundle\Entity\Contenu';

    /**
     * Retourne l'arbo des contenu de l'objet
     *
     * @param integer $id ID de l'objet
     *
     * @return array
     */
    public function getArboForObjet( $id )
    {
        $datas = new ArrayCollection( $this->getRepository()->getArboForObjet( $id )->getQuery()->getResult() );

        //Récupère uniquement les premiers parents
        $criteria = Criteria::create()->where(Criteria::expr()->eq("parent", null) );
        $parents  = $datas->matching( $criteria );
        
        //call recursive function to handle all datas
        return $this->_getArboRecursive($datas, $parents, array(), '' );
    }
    /**
     * 
     * Retourne l'arbo des contenu de l'objet
     *
     * @param array $id ID de l'objet
     *
     * @return array
     */
    public function getArboForObjets( $ids )
    {
        $datas = new ArrayCollection( $this->getRepository()->getArboForObjets( $ids )->getQuery()->getResult() );

        //Récupère uniquement les premiers parents
        $criteria = Criteria::create()->where(Criteria::expr()->eq("parent", null) );
        $parents  = $datas->matching( $criteria );
        
        //call recursive function to handle all datas
        $elems = $this->_getArboRecursive($datas, $parents, array(), '' );
        $return = array();
        foreach( $elems as $one ){
            if( $one->objet != null ){
                $return[ $one->objet ][] = $one;
            }
        }
        return $return;
    }

    /**
     * Formatte les références sous forme d'un unique tableau
     *
     * @param Contenu $contenu    Contenu concerné
     * @param array   $references Liste des références de type dictionnaire
     *
     * @return array
     */
    public function getReferences($contenu, $references)
    {
        $selectedReferences = $contenu->getReferences();
        $disabledChilds     = array();

        //applique les références 
        foreach( $selectedReferences as $selected )
        {
            //on récupère l'élément que l'on va manipuler
            $ref = $references[ $selected->getReference()->getId() ];

            //on le met à jour 
            $ref->selected = true;
            $ref->primary  = $selected->getPrimary();

            //si on est un enfant et que l'on est présent dans le tableau disabled childs, on devient disabled (car notre parent est sélectionné)
            if( in_array($ref->id, $disabledChilds))
                $ref->disabled = true;

            //si y'a des enfants, on ajoute les ids dans les disabledChilds
            if( !is_null($ref->childs) ){
                $childs         = json_decode($ref->childs);
                $disabledChilds = array_unique( array_merge($disabledChilds, $childs) );
            }

            //on remet l'élément à sa place
            $references[ $selected->getReference()->getId() ] = $ref;
        }
        
        return $references;
    }

    /**
     * Formatte les références sous forme d'un unique tableau
     *
     * @param Contenu $contenu    Contenu concerné
     * @param array   $references Liste des références de type dictionnaire
     *
     * @return array
     */
    public function getReferencesOwn($contenu)
    {
        $return = array();
        $selectedReferences = $contenu->getReferences();

        //applique les références 
        foreach( $selectedReferences as $selected )
        {
            $reference = $selected->getReference();

            //on remet l'élément à sa place
            $return[ $selected->getReference()->getId() ]['nom'] = $reference->getCode() . " - " . $reference->getLibelle();
            
            if( $reference->getParent() ){
                $return[ $reference->getParent()->getId() ]['childs'][] = $reference->getId();
            }
        }
        
        $this->formatReferencesOwn( $return );
        
        return $return;
    }

    /**
     * Retourne le nombre des contenus ayant le même alias
     *
     * @param Contenu $contenu Objet contenu
     *
     * @return integer
     */
    public function countAlias( $contenu )
    {
        return $this->getRepository()->countAlias($contenu)->getQuery()->getSingleScalarResult();
    }

    /**
     * Compte le nombre de contenu parents lié à l'objet
     *
     * @param Objet $objet Objet
     *
     * @return integer
     */
    public function countContenu( $objet )
    {
        return $this->getRepository()->countContenu($objet)->getQuery()->getSingleScalarResult();
    }

    /**
     * Retourne le prefix du contenu
     *
     * @param Contenu $contenu Contenu
     *
     * @return string
     */
    public function getPrefix($contenu)
    {
        return $this->_getPrefix( $contenu, '' );
    }

    /**
     * Retourne le prefix du contenu
     *
     * @param Contenu $contenu Contenu
     * @param string  $prefix  Prefix
     *
     * @return string
     */
    private function _getPrefix( $contenu, $prefix )
    {
        $prefix = $contenu->getOrder() . '.' . $prefix;

        if( !is_null($contenu->getParent()) )
            $prefix = $this->_getPrefix($contenu->getParent(), $prefix);
        
        return $prefix;
    }




    public function parseCsv( $csv, $objet )
    {
        //parse Str CSV and convert to array
        $lines    = explode("\n", $csv);
        if( empty($lines) ){
            return false;
        }

        //gestion des erreurs lors du parse du CSV
        $sommaire = array();
        foreach ($lines as $line) {
            $tmp = str_getcsv($line,';');
            if( isset($tmp[0]) && isset($tmp[1]) && !isset($tmp[2]) ){
                $sommaire[] = $tmp;
            } else {
                return false;
            }
        }

        //ajout des éléments parents only
        $parents = array();
        foreach($sommaire as $element)
        {
            $elem = &$parents;
            list($chapitre, $titre) = $element;
            $numeroChapitre = explode('.', $chapitre);
            foreach( $numeroChapitre as $key => $one ){
                if( $key == count($numeroChapitre) - 1 ){
                    $elem = &$elem[ $one ];
                } else {
                    $elem = &$elem[ $one ]['childs'];
                }
            }
            $elem['titre'] = $titre;
        }
        
        // clean elements sans titre
        $parents = $this->cleanSansTitre($parents);
        $this->saveContenusCSV($objet, $parents);

        return true;
    }










    /**
     * Fonction récursive qui parcourt l'ensemble des références en ajoutant l'element et recherchant les éventuels enfants
     *
     * @param ArrayCollection $items    Données d'origine
     * @param array           $elements Tableau d'élements à ajouter
     * @param array           $tab      Tableau de données vide à remplir puis retourner
     *
     * @return array
     */
    private function _getArboRecursive( ArrayCollection $items, $elements, $tab, $numeroChapitre )
    {        
        foreach($elements as $element)
        {
            //creation du mero de chapitre
            $chapitre = $numeroChapitre . $element->getOrder() . '.';

            //construction de l'element current
            $item             = new \stdClass;
            $item->titre      = $element->getTitre();
            $item->alias      = $element->getAlias();
            $item->id         = $element->getId();
            $item->references = count($element->getReferences());
            $item->order      = $chapitre;
            $item->hasContent = $element->getContenu() == '' ? false : true;
            $item->objet      = $element->getParent() == null ? $element->getObjet()->getId() : NULL;

            //add childs : filter items with current element
            $criteria     = Criteria::create()->where(Criteria::expr()->eq("parent", $element ))->orderBy(array("order"=>Criteria::ASC));
            $childs       = $items->matching( $criteria );
            $item->childs = $this->_getArboRecursive($items, $childs, array(), $chapitre );

            //add current item to big table
            $tab[] = $item;
        }

        //return big table
        return $tab;
    }
    
    private function formatReferencesOwn( &$retour ){
        foreach( $retour as $key => $one ){
            $retour[ $key ]['childs'] = $this->getChilds($retour, $one);
        }
    }
    
    private function getChilds(&$retour, $elem){
        if( isset( $elem['childs'] ) && count($elem['childs']) ){
            $childs = array();
            foreach( $elem["childs"] as $key => $one ){
                $childs[ $one ] = $retour[ $one ];
                $petitsEnfants = $this->getChilds($retour, $childs[ $one ]);
                if( $petitsEnfants ){
                    $childs[ $one ]['childs'] = $petitsEnfants;
                    unset( $retour[ $one ] );
                } else {
                    unset( $retour[ $one ] );
                }
            }
            return $childs;
        } else {
            return false;
        }
    }
    
    /**
     * Fonction qui renvoie uniquement les éléments du tableau ayant l'index "titre" qui existe, de manière récursive
     * 
     * @param array $elements
     * 
     * @return array 
     */
    private function cleanSansTitre($elements){
        $retour = array();
        foreach( $elements as $cle => $elem ){
            if( isset($elem['childs']) ){
                $elem['childs'] = $this->cleanSansTitre( $elem['childs'] );
            }
            if( isset($elem['titre']) ){
                $retour[$cle] = $elem;
            }
        }
        return $retour;
    }
    
    /**
     * Fonctionne qui sauvegarde tous les éléments du sommaire
     * 
     * @param type $objet
     * @param type $contenus
     * @param type $objects
     * @param type $parent
     * @param boolean doit-on sauvegarder $objects
     * 
     * @retun void
     */
    private function saveContenusCSV($objet, $contenus, &$objects = array(), $parent = null, $save = true){
        foreach( $contenus as $ordre => $content ){
            //créer un contenu (set titre, generate alias, set objet)
            $contenu = $this->createEmpty();
            $contenu->setObjet( $objet );
            $contenu->setTitre( $content['titre'] );
            $tool = new Chaine( $content['titre'] );
            $contenu->setAlias( $tool->minifie() );
            $contenu->setOrder($ordre);
            if( $parent ){
                $contenu->setParent($parent);
            }
            $objects[] = $contenu;
            if( isset($content['childs']) ){
                $this->saveContenusCSV($objet, $content['childs'], $objects, $contenu, false);
            }
        }
        
        if( $save ){
            $this->save($objects);
        }
    }
}