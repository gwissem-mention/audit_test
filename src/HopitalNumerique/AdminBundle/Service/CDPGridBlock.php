<?php

namespace HopitalNumerique\AdminBundle\Service;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\User;
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


    public function getBlockDatas(Domaine$domain)
    {
        $CDPDatas = [
            'members' => $this->userRepository->countCDPUsers($domain),
            'GTMembers' => $this->userRepository->countUsersInCDP($domain),
            'pendingRecords' => $this->ficheRepository->countPending($domain),
            'comments' => $this->commentaireRepository->getLatestCommentsCount($domain),
        ];

        return $CDPDatas;
    }
}
