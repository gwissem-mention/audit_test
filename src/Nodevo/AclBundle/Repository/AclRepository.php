<?php

namespace Nodevo\AclBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * AclRepository
 */
class AclRepository extends EntityRepository
{
    /**
     * Retourne toute la liste des Acls présentent en base
     *
     * @return QueryBuilder
     */
    public function getAcls()
    {
        $qb = $this->createQueryBuilder('acl');
        $qb->select('res.id as ressource, rol.id as role, acl.read, acl.write')
            ->leftJoin('acl.ressource', 'res')
            ->leftJoin('acl.role', 'rol');

        return $qb->getQuery();
    }
}