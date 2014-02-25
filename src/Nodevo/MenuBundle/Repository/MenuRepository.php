<?php

namespace Nodevo\MenuBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * MenuRepository
 */
class MenuRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     */
    public function getDatasForGrid( $condition )
    {        
        $qb = $this->_em->createQueryBuilder();
        $qb->select('menu.id, menu.name, menu.alias, menu.lock')
            ->from('\Nodevo\MenuBundle\Entity\Menu', 'menu');
                
        return $qb->getQuery()->getResult();
    }
}