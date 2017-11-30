<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Read;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;

/**
 * Class ReadRepository
 */
class ReadRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param User $user
     * @param Discussion $discussion
     *
     * @return Read|null
     */
    public function findOneByUserAndDiscussion(User $user, Discussion $discussion)
    {
        return $this->createQueryBuilder('read')
            ->andWhere('read.user = :user')->setParameter('user', $user)
            ->andWhere('read.discussion = :discussion')->setParameter('discussion', $discussion)

            ->setMaxResults(1)

            ->getQuery()->getOneOrNullResult()
        ;
    }
}
