<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\News\Retriever;

use Doctrine\ORM\NoResultException;
use HopitalNumerique\CommunautePratiqueBundle\DTO\News\PublicItem;
use HopitalNumerique\CommunautePratiqueBundle\Enum\ActivityEnum;
use HopitalNumerique\CommunautePratiqueBundle\Repository\ActivityRepository;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\ObjectIdentityRepository;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\CommunautePratiqueBundle\DTO\News\DiscussionItem;
use HopitalNumerique\CommunautePratiqueBundle\DTO\News\WallItemInterface;

class ActivityItemRetriever implements WallItemRetrieverInterface
{
    /**
     * @var ActivityRepository $activityRepository
     */
    protected $activityRepository;

    /**
     * @var ObjectIdentityRepository
     */
    protected $objectIdentityRepository;

    /**
     * DiscussionItemRetriever constructor.
     *
     * @param ActivityRepository $activityRepository
     * @param ObjectIdentityRepository $objectIdentityRepository
     */
    public function __construct(
        ActivityRepository $activityRepository,
        ObjectIdentityRepository $objectIdentityRepository
    ) {
        $this->activityRepository = $activityRepository;
        $this->objectIdentityRepository = $objectIdentityRepository;
    }

    /**
     * @param Domaine|null $domain
     *
     * @return WallItemInterface[]
     */
    public function retrieve(Domaine $domain = null)
    {
        $items = [];
        foreach ($this->activityRepository->getLatest($domain) as $activity) {
            try {
                $object = $this->objectIdentityRepository->populate($activity->getObjectIdentity());
            } catch (NoResultException $exception) {
                continue;
            }

            switch ($activity->getType()) {
                case ActivityEnum::TYPE_PUBLIC:
                    $items[] = new PublicItem($activity, $object);
                    break;
                default:
                    throw new \InvalidArgumentException();
            }
        }

        return $items;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return true;
    }
}
