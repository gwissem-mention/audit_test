<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Widget;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\UserBundle\Repository\UserRepository;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;
use HopitalNumerique\CommunautePratiqueBundle\Repository\GroupeRepository;

/**
 * Class DashboardWidget
 */
class DashboardWidget extends WidgetAbstract
{
    /**
     * @var UserRepository $userRepository
     */
    protected $userRepository;

    /**
     * @var GroupeRepository $groupRepository
     */
    protected $groupRepository;

    /**
     * @param UserRepository $userRepository
     * @param GroupeRepository $groupRepository
     */
    public function setRepositories(UserRepository $userRepository, GroupeRepository $groupRepository)
    {
        $this->userRepository = $userRepository;
        $this->groupRepository = $groupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $html = $this->twig->render('@HopitalNumeriqueCommunautePratique/Widget/widget.html.twig', [
            'cdpMembersCount' => $this->userRepository->countAddCDPUsers(),
            'groupsCount' => $this->groupRepository->countActiveGroups(),
            'userGroups' => $user->getCommunautePratiqueGroupes(),
        ]);

        $title = $this->translator->trans('title', [], 'cdpWidget');

        return new Widget('cdp', $title, $html);
    }
}
