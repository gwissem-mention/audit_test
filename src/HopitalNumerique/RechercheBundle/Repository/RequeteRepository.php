<?php

namespace HopitalNumerique\RechercheBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\RechercheBundle\Entity\Requete;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * RequeteRepository.
 */
class RequeteRepository extends EntityRepository
{
    public function countSavedRequestForUser(User $user)
    {
        return $this->_em->createQueryBuilder()
            ->select('COUNT(r) as nb')
            ->from(Requete::class, 'r')
            ->andWhere('r.user = :userId')->setParameter('userId', $user->getId())

            ->getQuery()->getSingleScalarResult()
        ;
    }
}
