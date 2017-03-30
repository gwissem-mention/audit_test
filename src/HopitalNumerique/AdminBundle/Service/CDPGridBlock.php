<?php

namespace HopitalNumerique\AdminBundle\Service;

use HopitalNumerique\UserBundle\Repository\UserRepository;
use HopitalNumerique\CommunautePratiqueBundle\Repository\FicheRepository;
use HopitalNumerique\CommunautePratiqueBundle\Repository\CommentaireRepository;

class CDPGridBlock
{
    /** @var UserRepository $userRepository */
    protected $userRepository;
    /** @var CommentaireRepository $commentaireRepository */
    protected $commentaireRepository;
    /** @var FicheRepository $ficheRepository */
    protected $ficheRepository;

    /**
     * CDPGridBlock constructor.
     *
     * @param UserRepository $userRepository
     * @param CommentaireRepository $commentaireRepository
     * @param FicheRepository $ficheRepository
     */
    public function __construct(UserRepository $userRepository, CommentaireRepository $commentaireRepository, FicheRepository $ficheRepository)
    {
        $this->userRepository = $userRepository;
        $this->commentaireRepository = $commentaireRepository;
        $this->ficheRepository = $ficheRepository;
    }


    public function getBlockDatas()
    {
        $CDPDatas = [
            'members' => $this->userRepository->countCDPUsers(),
            'GTMembers' => $this->userRepository->countUsersInCDP(),
            'pendingRecords' => $this->ficheRepository->countPending(),
            'comments' => $this->commentaireRepository->getLatestCommentsCount(),
        ];

        return $CDPDatas;
    }
}
