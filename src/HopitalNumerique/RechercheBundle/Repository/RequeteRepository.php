<?php

namespace HopitalNumerique\RechercheBundle\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityRepository;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\RechercheBundle\Entity\Requete;

/**
 * RequeteRepository.
 */
class RequeteRepository extends EntityRepository
{
    /**
     * @param User $user
     *
     * @return mixed
     */
    public function countSavedRequestForUser(User $user)
    {
        return $this->_em->createQueryBuilder()
            ->select('COUNT(r) as nb')
            ->from(Requete::class, 'r')
            ->andWhere('r.user = :userId')->setParameter('userId', $user->getId())

            ->getQuery()->getSingleScalarResult()
        ;
    }

    /**
     * @param User $user
     * @param Domaine[]|null $domains
     *
     * @return Requete[]
     */
    public function getSavedSearchesByUser(User $user, $domains = null)
    {
        $qb = $this->createQueryBuilder('request')
            ->join('request.user', 'user', Join::WITH, 'user = :userId')
            ->setParameter('userId', $user->getId())
        ;

        if (null !== $domains) {
            $qb->join('request.domaine', 'domain', Join::WITH, 'domain.id IN (:domains)')
               ->setParameter('domains', $domains)
            ;
        }

        return $qb
            ->orderBy('request.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
