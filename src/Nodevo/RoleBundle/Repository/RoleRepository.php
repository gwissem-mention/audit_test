<?php

namespace Nodevo\RoleBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * RoleRepository
 */
class RoleRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     */
    public function getDatasForGrid()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('role.id, role.name, role.initial, refEtat.libelle as etat ')
            ->from('NodevoRoleBundle:Role', 'role')
            ->leftJoin('role.etat','refEtat')
            ->orderBy('role.name');
            
        return $qb->getQuery()->getResult();
    }
}