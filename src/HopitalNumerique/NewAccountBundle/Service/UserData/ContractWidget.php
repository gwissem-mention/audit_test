<?php

namespace HopitalNumerique\NewAccountBundle\Service\UserData;

use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\NewAccountBundle\Security\InformationsAccessVoter;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetInterface;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareTrait;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareInterface;
use HopitalNumerique\UserBundle\Repository\ContractualisationRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ContractWidget
 */
class ContractWidget implements WidgetInterface, DomainAwareInterface
{
    use DomainAwareTrait;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var ContractualisationRepository
     */
    protected $contractRepository;

    /**
     * ContractWidget constructor.
     *
     * @param \Twig_Environment $twig
     * @param TokenStorageInterface $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TranslatorInterface $translator
     * @param ContractualisationRepository $contractRepository
     */
    public function __construct(
        \Twig_Environment $twig,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        TranslatorInterface $translator,
        ContractualisationRepository $contractRepository
    ) {
        $this->twig = $twig;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->translator = $translator;
        $this->contractRepository = $contractRepository;
    }

    /**
     * @return Widget|null
     */
    public function getWidget()
    {
        if (!$this->authorizationChecker->isGranted(InformationsAccessVoter::ACCESS_CONTRACTS)) {
            return null;
        }

        $user = $this->tokenStorage->getToken()->getUser();

        return new Widget(
            'contract',
            $this->translator->trans('account.profile.contracts'),
            $this->twig->render('@NewAccount/profile/tabs/contracts.html.twig', [
                'contracts' => $this->contractRepository->findByUser($user->getId()),
            ])
        );
    }
}
