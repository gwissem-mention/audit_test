<?php

namespace HopitalNumerique\CoreBundle\Repository\ObjectIdentity;

use Doctrine\ORM\EntityRepository;
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
}
