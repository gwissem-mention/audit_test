<?php

namespace Nodevo\MenuBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;
use Nodevo\MenuBundle\Provider\MenuNode;
use Nodevo\MenuBundle\Entity\Item;
use Nodevo\MenuBundle\Entity\Menu;

use Doctrine\ORM\PersistentCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Manager de l'entité Menu
 */
class MenuManager extends BaseManager
{
    protected $_class = '\Nodevo\MenuBundle\Entity\Menu';

    /**
     * Récupérer une instance de menu via son Alias
     * 
     * @param  string $alias Alias identifiant un menu
     * 
     * @return Menu
     */
    public function findOneByAlias( $alias )
    {
        return $this->getRepository()->findOneByAlias( $alias );
    }

    /**
     * Force la regénération du menu
     * 
     * @param  Menu   $menu Menu de l'arbre à regénérer
     */
    public function refreshTree( Menu $menu )
    {
        //refresh item order
        $items = $menu->getItems();
        $this->refreshItemOrder($items);   
        $this->save($menu);

        //refresh cache
        $this->getTree( $menu, true );
    }

    /**
     * Récupération de l'arbre des items du menu entré en paramètre
     * 
     * @param  Menu    $menu  Menu de l'arbre à récupérer
     * @param  boolean $force Forcer la regénération du menu
     * 
     * @return MenuNode Arbre du menu
     */
    public function getTree( Menu $menu, $force = false )
    {
        $cache          = $this->getCache();
        $cacheMenuLabel = 'menu_tree_'.$menu->getId();

        //Si on force la regénération ou que le cache n'a pas d'entrée pour "tree"
        if( (false === ($cached_data = $cache->fetch($cacheMenuLabel))) || ($force) ) {
            //Si le cache du menu existe, il faut le vider
            if ($cache->contains($cacheMenuLabel))
                $cache->delete($cacheMenuLabel);
            
            //Régénération de l'arbre
            $itemsCollection = $menu->getItems();

            $tree = new MenuNode();
            $tree = $this->addNodeChilds( $tree, $itemsCollection, null );

            $cached_data = $tree;

            //Sauvegarde du cache
            $cache->save($cacheMenuLabel, $cached_data );
        }

        return $cached_data;
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( $condition = null )
    {        
        return $this->getRepository()->getDatasForGrid( $condition );
    } 







    /**
     * Add childs recursively to a node
     * 
     * @param MenuNode             $tree       [description]
     * @param PersistentCollection $collection [description]
     * @param Item                 $node       [description]
     */
    private function addNodeChilds( MenuNode $tree, PersistentCollection $collection, Item $node = null)
    {
        $collection->initialize();

        $childs = $this->getItemChildsFromCollection($collection, $node);

        foreach( $childs as $one ) {
            $menuNode = new MenuNode( $one );
            $tree->addChildren($menuNode);
            $this->addNodeChilds( $menuNode, $collection, $one );
        }

        return $tree;
    }

    /*
     * Raffraîchit l'ordre des items enfants d'un $item
     */
    private function refreshItemOrder( PersistentCollection $items, Item $item = null)
    {
        $childs   = $this->getItemChildsFromCollection($items, $item);
        $nbChilds = count($childs);
        
        for ($i=0; $i < $nbChilds; $i++) { 
            $childs[$i]->setOrder($i+1);
            $this->refreshItemOrder($items, $childs[$i]);
        }
    }

    /*
     * Récupère dans une collection d'$items tous les items qui ont pour parent $parent
     */
    private function getItemChildsFromCollection( PersistentCollection $items, Item $parent = null)
    {
        //si le parent n'existe pas, on filtre dans la collection pour récupérer TOUS les items SANS parents
        if (null === $parent){
            $criteria = Criteria::create()
                                        ->where(Criteria::expr()->eq("parent", $parent))
                                        ->orderBy( array("order" => Criteria::ASC) );
            $childs = $items->matching( $criteria );
        //si le parent existe, on récupère tous ses enfants
        } else 
            $childs = $parent->getChildsFromCollection( $items );

        return $childs;
    }
}