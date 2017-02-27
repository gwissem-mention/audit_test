<?php

namespace Nodevo\MenuBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;

class ItemManager extends BaseManager
{
    protected $class = '\Nodevo\MenuBundle\Entity\Item';

    public function __construct(EntityManager $em, $menuManager)
    {
        $this->em           = $em;
        $this->_menuManager = $menuManager;
        $this->repository   = $this->em->getRepository($this->class);
    }

    /**
     * Mise à jour de l'order des items du menu $menu
     *
     * @param $item \Nodevo\MenuBundle\Entity\Item Element de menu à mettre à jour
     */
    public function updateOrder($item)
    {
        //Récupération du menu
        $menu = $this->repository->findAllOrderSuperieurByItem($item)->getOneOrNullResult();

        //Récupération des items du menu si le menu n'est pas vide
        $items = ($menu != null) ? $menu->getItems() : [];

        //On décale les items dont l'order est supérieur ou égal à l'item courant
        $newOrder = $item->getOrder();
        foreach ($items as $itemAModifier) {
            $newOrder++;
            $itemAModifier->setOrder($newOrder);
            $this->save($itemAModifier);
        }
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @param \StdClass $condition
     *
     * @return array
     */
    public function getDatasForGrid(\StdClass $condition = null)
    {
        return $this->getRepository()->getDatasForGrid($condition);
    }
}
