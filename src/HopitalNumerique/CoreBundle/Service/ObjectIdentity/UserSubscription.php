<?php

namespace HopitalNumerique\CoreBundle\Service\ObjectIdentity;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CoreBundle\Event\UserUnsubscribeEvent;
use HopitalNumerique\CoreBundle\Event\UserSubscribeEvent;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\SubscriptionRepository;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\ObjectIdentityRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * UserSubscription constructor.
     *
     * @param ObjectIdentityRepository $objectIdentityRepository
     * @param SubscriptionRepository $subscriptionRepository
     * @param EntityManagerInterface $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ObjectIdentityRepository $objectIdentityRepository,
        SubscriptionRepository $subscriptionRepository,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->objectIdentityRepository = $objectIdentityRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param $object
     * @param User $user
     */
    public function subscribe($object, User $user)
    {
        $objectIdentity = $this->objectIdentityRepository->findOrCreate(ObjectIdentity::createFromDomainObject($object));

        $this->subscriptionRepository->subscribe($objectIdentity, $user);

        $event = new UserSubscribeEvent($object, $user);
        $this->eventDispatcher->dispatch(self::SUBSCRIBE, $event);
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

        $event = new UserUnsubscribeEvent($object, $user);
        $this->eventDispatcher->dispatch(self::UNSUBSCRIBE, $event);
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
