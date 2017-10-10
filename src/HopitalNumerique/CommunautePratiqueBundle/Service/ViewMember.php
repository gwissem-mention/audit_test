<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Member\ViewedMember;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Member\ViewedMemberRepository;

/**
 * Class ViewMember
 */
class ViewMember
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var ViewedMemberRepository $viewedMemberRepository
     */
    protected $viewedMemberRepository;

    /**
     * ViewMember constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ViewedMemberRepository $viewedMemberRepository
     */
    public function __construct(EntityManagerInterface $entityManager, ViewedMemberRepository $viewedMemberRepository)
    {
        $this->entityManager = $entityManager;
        $this->viewedMemberRepository = $viewedMemberRepository;
    }

    /**
     * Set a cdp member profile viewed by another member
     *
     * @param User $member
     * @param User $viewer
     */
    public function viewMember(User $member, User $viewer)
    {
        /** @var ViewedMember $view */
        if ($view = $this->viewedMemberRepository->findOneBy(['member' => $member, 'viewer' => $viewer])) {
            $view->setViewedAt(new \DateTime());
        } else {
            $view = new ViewedMember($member, $viewer, new \DateTime());
            $this->entityManager->persist($view);
        }

        $this->entityManager->flush();
    }
}
