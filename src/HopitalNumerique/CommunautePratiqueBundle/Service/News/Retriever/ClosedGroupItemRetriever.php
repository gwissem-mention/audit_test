<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\News\Retriever;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\CommunautePratiqueBundle\DTO\News\ClosedGroupItem;
use HopitalNumerique\CommunautePratiqueBundle\DTO\News\WallItemInterface;
use HopitalNumerique\CommunautePratiqueBundle\Repository\GroupeRepository;

class ClosedGroupItemRetriever implements WallItemRetrieverInterface
{
    /**
     * @var GroupeRepository $groupRepository
     */
    protected $groupRepository;

    /**
     * GroupeItemRetriever constructor.
     *
     * @param GroupeRepository $groupRepository
     */
    public function __construct(GroupeRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    /**
     * @param Domaine|null $domain
     *
     * @return WallItemInterface[]
     */
    public function retrieve(Domaine $domain = null)
    {
        $items = [];
        foreach ($this->groupRepository->getLastClosed($domain) as $discussion) {
            $items[] = new ClosedGroupItem($discussion);
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
