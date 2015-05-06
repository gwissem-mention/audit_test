<?php

namespace HopitalNumerique\DomaineBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * DomaineRepository
 */
class DomaineRepository extends EntityRepository
{
    public function getDomainesUserConnectedForForm($idUser)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('domaine')
            ->from('HopitalNumeriqueDomaineBundle:Domaine', 'domaine')
            ->leftJoin('domaine.users','user')
            ->where('user.id = :idUser')
            ->setParameter('idUser', $idUser);
            
        return $qb;
    }
}