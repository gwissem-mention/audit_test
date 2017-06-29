<?php

namespace HopitalNumerique\AdminBundle\Service;

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
     * CDPGridBlock constructor.
     *
     * @param UserRepository $userRepository
     * @param CommentaireRepository $commentaireRepository
     * @param FicheRepository $ficheRepository
     */
    public function __construct(
        UserRepository $userRepository,
        CommentaireRepository $commentaireRepository,
        FicheRepository $ficheRepository
    ) {
        $this->userRepository = $userRepository;
        $this->commentaireRepository = $commentaireRepository;
        $this->ficheRepository = $ficheRepository;
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
}
