<?php

namespace HopitalNumerique\AdminBundle\Service;

use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\MessageRepository;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Repository\UserRepository;
use HopitalNumerique\CommunautePratiqueBundle\Repository\FicheRepository;
use HopitalNumerique\CommunautePratiqueBundle\Repository\CommentaireRepository;

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
     * @var CommentaireRepository $commentaireRepository
     */
    protected $commentaireRepository;

    /**
     * @var FicheRepository $ficheRepository
     */
    protected $ficheRepository;

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
     * @param CommentaireRepository $commentaireRepository
     * @param FicheRepository $ficheRepository
     * @param DiscussionRepository $discussionRepository
     * @param MessageRepository $messageRepository
     */
    public function __construct(
        UserRepository $userRepository,
        CommentaireRepository $commentaireRepository,
        FicheRepository $ficheRepository,
        DiscussionRepository $discussionRepository,
        MessageRepository $messageRepository
    ) {
        $this->userRepository = $userRepository;
        $this->commentaireRepository = $commentaireRepository;
        $this->ficheRepository = $ficheRepository;
        $this->discussionRepository = $discussionRepository;
        $this->messageRepository = $messageRepository;
    }

    /**
     * @param Domaine[] $domains
     *
     * @return array
     */
    public function getBlockDatas($domains)
    {
        $CDPDatas = [
            'members' => $this->userRepository->countCDPUsers($domains),
            'GTMembers' => $this->userRepository->countUsersInCDP($domains),
            'pendingRecords' => $this->ficheRepository->countPending($domains),
            'comments' => $this->commentaireRepository->getLatestCommentsCount($domains),
        ];

        return $CDPDatas;
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
        ];
    }
}
