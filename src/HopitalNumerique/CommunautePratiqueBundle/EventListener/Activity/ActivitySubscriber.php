<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener\Activity;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Activity;
use HopitalNumerique\CommunautePratiqueBundle\Enum\ActivityEnum;
use HopitalNumerique\CommunautePratiqueBundle\Event\Activity\ActivityRegistrationEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Repository\ActivityRepository;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\ObjectIdentityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActivitySubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ActivityRepository
     */
    protected $activityRepository;

    /**
     * @var ObjectIdentityRepository
     */
    protected $objectIdentityRepository;

    /**
     * ActivitySubscriber constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ActivityRepository $activityRepository
     * @param ObjectIdentityRepository $objectIdentityRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ActivityRepository $activityRepository,
        ObjectIdentityRepository $objectIdentityRepository
    ) {
        $this->entityManager = $entityManager;
        $this->activityRepository = $activityRepository;
        $this->objectIdentityRepository = $objectIdentityRepository;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::DISCUSSION_PUBLIC => [
                ['savePublicActivity'],
            ],
        ];
    }

    /**
     * @param ActivityRegistrationEvent $event
     */
    public function savePublicActivity(ActivityRegistrationEvent $event)
    {
        $objectIdentity = $this->objectIdentityRepository->findOrCreate(
            ObjectIdentity::createFromDomainObject($event->getDiscussion())
        );

        $activity = $this->activityRepository->findOneByObjectIdentity($objectIdentity);
        if (empty($activity)) {
            $this->entityManager->persist(
                new Activity(
                    ActivityEnum::TYPE_PUBLIC,
                    $objectIdentity
                )
            );

            $this->entityManager->flush();
        }
    }
}
