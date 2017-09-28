<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Discussion;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Read;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\ReadRepository;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\MessageRepository;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository;

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
     * @var MessageRepository $messageRepository
     */
    protected $messageRepository;

    /**
     * @var Read[] $userReadings
     */
    private $userReadings;

    /**
     * NewDiscussionActivityCounter constructor.
     *
     * @param ReadRepository $readRepository
     * @param DiscussionRepository $discussionRepository
     * @param MessageRepository $messageRepository
     */
    public function __construct(ReadRepository $readRepository, DiscussionRepository $discussionRepository, MessageRepository $messageRepository)
    {
        $this->readRepository = $readRepository;
        $this->discussionRepository = $discussionRepository;
        $this->messageRepository = $messageRepository;
    }

    /**
     * @param Groupe $group
     * @param User $user
     *
     * @return int
     */
    public function getNewDiscussionCount(Groupe $group, User $user) {
        return count($this->discussionRepository->getDiscussionNotReaded($group, $user));
    }

    /**
     * @param Groupe $group
     * @param User $user
     * 
     * @return int
     */
    public function getNewMessageCount(Groupe $group, User $user) {
        return count($this->messageRepository->getMessageNotReaded($group, $user));
    }

    public function getNewDocumentCount(User $user) {}

    private function getUserReadings(User $user)
    {
        return $this->userReadings ?: $this->readRepository->findByUser($user);
    }
}
