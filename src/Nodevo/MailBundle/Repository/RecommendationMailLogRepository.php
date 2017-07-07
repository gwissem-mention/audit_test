<?php

namespace Nodevo\MailBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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

    /**
     * @return array
     */
    public function countGroupByUser()
    {
        $results = $this->createQueryBuilder('rl')
            ->select('COUNT(rl) as recommendationsCount, u.id')
            ->join('rl.sendedBy', 'u')

            ->addGroupBy('u.id')

            ->getQuery()->getResult()
        ;

        $recommendationsCount = [];
        foreach ($results as $result) {
            $recommendationsCount[$result['id']] = (int) $result['recommendationsCount'];
        }

        return $recommendationsCount;
    }
}
