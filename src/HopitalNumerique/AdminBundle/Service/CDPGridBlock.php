<?php

namespace HopitalNumerique\AdminBundle\Service;

use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\MessageRepository;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Repository\UserRepository;

/**
 * Class CDPGridBlock
 */
class CDPGridBlock
{
    /**
     * @var UserRepository $userRepository
     */
    protected $userRepository;

    /**
     * @var DiscussionRepository $discussionRepository
     */
    protected $discussionRepository;

    /**
     * @var MessageRepository $messageRepository
     */
    protected $messageRepository;

    /**
     * CDPGridBlock constructor.
     *
     * @param UserRepository $userRepository
     * @param DiscussionRepository $discussionRepository
     * @param MessageRepository $messageRepository
     */
    public function __construct(
        UserRepository $userRepository,
        DiscussionRepository $discussionRepository,
        MessageRepository $messageRepository
    ) {
        $this->userRepository = $userRepository;
        $this->discussionRepository = $discussionRepository;
        $this->messageRepository = $messageRepository;
    }

    /**
     * @param Domaine[] $domains
     *
     * @return array
     */
    public function getBlockDiscussionDatas($domains)
    {
        return [
            'active' => $this->discussionRepository->countActiveDiscussions($domains),
            'messages' => $this->messageRepository->countRecentMessages($domains),
            'withoutReply' => $this->discussionRepository->countDiscussionWithoutReply($domains),
            'members' => $this->userRepository->countCDPUsers($domains),
            'GTMembers' => $this->userRepository->countUsersInCDP($domains),
        ];
    }
}
