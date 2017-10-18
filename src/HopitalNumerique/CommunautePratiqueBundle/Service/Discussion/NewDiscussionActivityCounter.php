<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Discussion;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\MessageRepository;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository;

class NewDiscussionActivityCounter
{

    /**
     * @var DiscussionRepository $discussionRepository
     */
    protected $discussionRepository;

    /**
     * @var MessageRepository $messageRepository
     */
    protected $messageRepository;

    /**
     * NewDiscussionActivityCounter constructor.
     *
     * @param DiscussionRepository $discussionRepository
     * @param MessageRepository $messageRepository
     */
    public function __construct(DiscussionRepository $discussionRepository, MessageRepository $messageRepository)
    {
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

    /**
     * @param Groupe $group
     * @param User $user
     *
     * @return int
     */
    public function getNewDocumentCount(Groupe $group, User $user) {
        $count = 0;

        /** @var Message $message */
        foreach ($this->messageRepository->getMessageNotReaded($group, $user) as $message) {
            $count += $message->getFiles()->count();
        }

        return $count;
    }
}
