<?php

namespace HopitalNumerique\FlashBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FlashRepository
 */
class FlashRepository extends EntityRepository
{
    /**
     * Récupère les messages visibles par l'utilisateur
     *
     * @return QueryBuilder
     */
    public function getMessagesForUser( $user )
    {
        $role = $user->getRole();
        $qb   = $this->_em->createQueryBuilder();

        $qb->select('fla')
            ->from('HopitalNumeriqueFlashBundle:Flash', 'fla')
            ->leftJoin('fla.roles','role')
            ->andWhere('role.role = :role', 'fla.isPublished = 1')
            ->setParameter('role', $role)
            ->orderBy('fla.dateCreation', 'asc');
            
        return $qb;
    }
}
