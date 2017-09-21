<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\DomaineBundle\Repository\DomaineRepository;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class AvailableDomainsRetriever
 */
class AvailableDomainsRetriever
{
    /**
     * @var DomaineRepository $domainRepository
     */
    protected $domainRepository;

    /**
     * @var CurrentDomaine $currentDomain
     */
    protected $currentDomain;

    /**
     * @var AuthorizationCheckerInterface $authorizationChecker
     */
    protected $authorizationChecker;

    /**
     * @var TokenStorageInterface $tokenStorage
     */
    protected $tokenStorage;

    /**
     * AvailableDomainsRetriever constructor.
     *
     * @param DomaineRepository $domainRepository
     * @param CurrentDomaine $currentDomain
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        DomaineRepository $domainRepository,
        CurrentDomaine $currentDomain,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->domainRepository = $domainRepository;
        $this->currentDomain = $currentDomain;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array
     */
    public function getAvailableDomains()
    {
        if (!$this->authorizationChecker->isGranted('ROLE_USER')) {
            return [$this->currentDomain->get()];
        }

        return $this->tokenStorage->getToken()->getUser()->getDomaines()->filter(function (Domaine $domain) {
            return $domain->getCommunautePratiqueGroupes()->count();
        })->toArray();
    }
}
