<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\News\Retriever;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Repository\UserRepository;
use HopitalNumerique\CommunautePratiqueBundle\DTO\News\NewMemberItem;
use HopitalNumerique\CommunautePratiqueBundle\DTO\News\WallItemInterface;

class NewMemberItemRetriever implements WallItemRetrieverInterface
{
    /**
     * @var UserRepository $userRepository
     */
    protected $userRepository;

    /**
     * GroupeItemRetriever constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param Domaine|null $domain
     *
     * @return WallItemInterface[]
     */
    public function retrieve(Domaine $domain = null)
    {
        $items = [];
        foreach ($this->userRepository->getLastUserEnrolledInCDP($domain) as $user) {
            $items[] = new NewMemberItem($user);
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
