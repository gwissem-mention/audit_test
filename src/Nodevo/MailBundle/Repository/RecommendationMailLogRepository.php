<?php

namespace Nodevo\MailBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\MailBundle\Entity\RecommendationMailLog;

class RecommendationMailLogRepository extends EntityRepository
{
    /**
     * @param User $user
     *
     * @return integer
     */
    public function countForUser(User $user)
    {
        return $this->_em->createQueryBuilder()
            ->select('COUNT(rl)')
            ->from(RecommendationMailLog::class, 'rl')
            ->andWhere('rl.sendedBy = :userId')->setParameter('userId', $user->getId())

            ->getQuery()->getSingleScalarResult()
        ;
    }
}
