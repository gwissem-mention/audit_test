<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\News\Retriever;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\CommunautePratiqueBundle\DTO\News\ClosedGroupItem;
use HopitalNumerique\CommunautePratiqueBundle\DTO\News\WallItemInterface;
use HopitalNumerique\CommunautePratiqueBundle\Repository\GroupeRepository;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ClosedGroupItemRetriever implements WallItemRetrieverInterface
{
    /**
     * @var GroupeRepository $groupRepository
     */
    protected $groupRepository;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * GroupeItemRetriever constructor.
     *
     * @param GroupeRepository $groupRepository
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(GroupeRepository $groupRepository, TokenStorageInterface $tokenStorage)
    {
        $this->groupRepository = $groupRepository;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param Domaine|null $domain
     *
     * @return WallItemInterface[]
     */
    public function retrieve(Domaine $domain = null)
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $items = [];
        foreach ($this->groupRepository->getLastClosed($domain, 20,$user instanceof User ? $user : null) as $discussion) {
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
