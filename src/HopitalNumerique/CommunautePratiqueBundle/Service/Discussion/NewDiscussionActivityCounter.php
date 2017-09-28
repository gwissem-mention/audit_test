<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Discussion;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Read;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\ReadRepository;
use HopitalNumerique\UserBundle\Entity\User;

class NewDiscussionActivityCounter
{
    /**
     * @var ReadRepository $readRepository
     */
    protected $readRepository;

    /**
     * @var DiscussionRepository $discussionRepository
     */
    protected $discussionRepository;

    /**
     * @var Read[] $userReadings
     */
    private $userReadings;

    /**
     * NewDiscussionActivityCounter constructor.
     *
     * @param ReadRepository $readRepository
     * @param DiscussionRepository $discussionRepository
     */
    public function __construct(ReadRepository $readRepository, DiscussionRepository $discussionRepository)
    {
        $this->readRepository = $readRepository;
        $this->discussionRepository = $discussionRepository;
    }

    public function getNewDiscussionCount(Groupe $group, User $user) {
        return count($this->discussionRepository->getDiscussionNotReaded($group, $user));
    }

    public function getNewMessageCount(User $user) {}

    public function getNewDocumentCount(User $user) {}

    private function getUserReadings(User $user)
    {
        return $this->userReadings ?: $this->readRepository->findByUser($user);
    }
}
