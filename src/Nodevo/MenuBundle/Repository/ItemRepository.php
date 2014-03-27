<?php

namespace Nodevo\MenuBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * MenuRepository
 */
class ItemRepository extends EntityRepository
{
    /**
     * Récupère le menu et les items d'odre supérieur à l'item modifié
     *
     * @param \Nodevo\MenuBundle\Entity\item $item Item modifié
     *
     * @return array
     */
    public function findAllOrderSuperieurByItem( $item )
    {
    	//Récupération du menu
        $menu     = $item->getMenu();
        $idParent = $item->getParent() ? $item->getParent()->getId() : null;
    	
        $qb = $this->_em->createQueryBuilder();
        $qb->select('m', 'i')
            ->from('\Nodevo\MenuBundle\Entity\Menu', 'm')
            ->leftJoin('m.items', 'i')
            ->where( 'm.id = :idMenu AND i.order >= :indexOrder AND i.id != :idItem')
            ->setParameter('idMenu', $menu->getId() )
            ->setParameter('idItem', $item->getId() )
            ->setParameter('indexOrder', $item->getOrder() );

        if (is_null($idParent))
            $qb->andWhere('i.parent IS NULL');
        else {
            $qb->andWhere('i.parent = :idParent')
               ->setParameter('idParent', $idParent);
        }

        return $qb->getQuery();
    }    

    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     */
    public function getDatasForGrid( $condition )
    {        
        $qb = $this->_em->createQueryBuilder();
        $qb->select('item.id, item.name, item.display, item.order, itemParent.id as idItemParent ')
            ->from('\Nodevo\MenuBundle\Entity\Menu', 'menu')
            ->leftJoin('menu.items', 'item')
            ->leftJoin('item.parent', 'itemParent')
            ->where( 'menu.id = :idMenu')
            ->setParameter('idMenu', $condition->value )
            ->orderBy('item.order');
                
        return $qb->getQuery()->getResult();
    }
}
