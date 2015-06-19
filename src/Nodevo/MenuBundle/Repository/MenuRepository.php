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
     * @return Query Builder
     */
    public function getDatasForGrid($domainesIds, $condition = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('menu.id, menu.alias, menu.name, menu.lock')
            ->from('NodevoMenuBundle:Menu', 'menu')
            ->orderBy('menu.name');
            
        return $qb;
    }
}