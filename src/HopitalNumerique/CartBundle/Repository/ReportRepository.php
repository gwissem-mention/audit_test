<?php

namespace HopitalNumerique\CartBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\CartBundle\Entity\ReportSharing;
use HopitalNumerique\UserBundle\Entity\User;

class ReportRepository extends EntityRepository
{

    /**
     * @param User $owner
     *
     * @return array
     */
    public function findAllForUser(User $owner)
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.items', 'ri')->addSelect('ri')
            ->join('r.owner', 'u')->addSelect('u')
            ->leftJoin('r.shares', 'rs', Join::WITH, 'rs.target = :userId AND rs.type = :shareType')->addSelect('rs')
            ->where('u.id = :userId')
            ->orWhere('rs.id IS NOT NULL')

            ->setParameter('userId', $owner->getId())
            ->setParameter('shareType', ReportSharing::TYPE_SHARE)

            ->addOrderBy('r.updatedAt', 'DESC')

            ->getQuery()->getResult()
        ;
    }
}
