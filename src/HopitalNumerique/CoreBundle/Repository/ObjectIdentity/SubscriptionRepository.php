<?php

namespace HopitalNumerique\CoreBundle\Repository\ObjectIdentity;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\Subscription;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;

class SubscriptionRepository extends EntityRepository
{
    /**
     * @param ObjectIdentity $objectIdentity
     * @param User $user
     */
    public function subscribe(ObjectIdentity $objectIdentity, User $user)
    {
        $subscription = $this->findOneBy(['objectIdentity' => $objectIdentity, 'user' => $user]);

        if (!$subscription) {
            $subscription = new Subscription($objectIdentity, $user);
            $this->_em->persist($subscription);
            $this->_em->flush($subscription);
        }
    }

    /**
     * @param ObjectIdentity $objectIdentity
     *
     * @return User[]
     */
    public function findSubscribers(ObjectIdentity $objectIdentity)
    {
        return $this->_em->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->join(Subscription::class, 'subscription', Join::WITH, 'subscription.user = user.id')
            ->join('subscription.objectIdentity', 'objectIdentity', Join::WITH, 'objectIdentity.id = :objectIdentity')
            ->setParameter('objectIdentity', $objectIdentity)

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param ObjectIdentity $objectIdentity
     *
     * @return QueryBuilder
     */
    public function findSubscribersQueryBuilder(ObjectIdentity $objectIdentity)
    {
        return $this->_em->createQueryBuilder()
            ->select('user.id')
            ->from(User::class, 'user')
            ->join(Subscription::class, 'subscription', Join::WITH, 'subscription.user = user.id')
            ->join('subscription.objectIdentity', 'objectIdentity', Join::WITH, 'objectIdentity.id = :objectIdentity')
            ->setParameter('objectIdentity', $objectIdentity)
        ;
    }
}
