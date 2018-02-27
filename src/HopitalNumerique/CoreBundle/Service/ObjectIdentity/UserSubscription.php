<?php

namespace HopitalNumerique\CoreBundle\Service\ObjectIdentity;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\SubscriptionRepository;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\ObjectIdentityRepository;

/**
 * Class UserSubscription
 *
 * Subscribe a user to an object identity
 */
class UserSubscription
{
    const SUBSCRIBE = 'subscribe';
    const UNSUBSCRIBE = 'unsubscribe';

    /**
     * @var ObjectIdentityRepository
     */
    protected $objectIdentityRepository;

    /**
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * UserSubscription constructor.
     *
     * @param ObjectIdentityRepository $objectIdentityRepository
     * @param SubscriptionRepository $subscriptionRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        ObjectIdentityRepository $objectIdentityRepository,
        SubscriptionRepository $subscriptionRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->objectIdentityRepository = $objectIdentityRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param $object
     * @param User $user
     */
    public function subscribe($object, User $user)
    {
        $objectIdentity = $this->objectIdentityRepository->findOrCreate(ObjectIdentity::createFromDomainObject($object));

        $this->subscriptionRepository->subscribe($objectIdentity, $user);
    }

    /**
     * @param mixed $object
     * @param User $user
     */
    public function unsubscribe($object, User $user)
    {
        $subscription = $this->subscriptionRepository->findOneBy(['objectIdentity' => ObjectIdentity::createFromDomainObject($object), 'user' => $user]);

        if ($subscription) {
            $this->entityManager->remove($subscription);
            $this->entityManager->flush();
        }
    }

    /**
     * @param mixed $object
     * @param User $user
     *
     * @return bool
     */
    public function isSubscribed($object, User $user)
    {
        return null !== $this->subscriptionRepository->findOneBy(['objectIdentity' => ObjectIdentity::createFromDomainObject($object), 'user' => $user]);
    }

    /**
     * @param mixed $object
     *
     * @return bool
     */
    public function listSubscribed($object)
    {
        return $this->subscriptionRepository->findBy(['objectIdentity' => ObjectIdentity::createFromDomainObject($object)]);
    }
}
