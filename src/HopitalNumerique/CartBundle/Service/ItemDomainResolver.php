<?php

namespace HopitalNumerique\CartBundle\Service;

use HopitalNumerique\CartBundle\Model\DomainInterface;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\DomaineBundle\Repository\DomaineRepository;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Allows to search domain for items implementing DomainInterface.
 */
class ItemDomainResolver
{
    /**
     * @var RequestStack $requestStack
     */
    protected $requestStack;

    /**
     * @var TokenStorageInterface $securityTokenStorage
     */
    protected $tokenStorage;

    /**
     * @var DomaineRepository $domainRepository
     */
    protected $domainRepository;

    /**
     * ItemDomainResolver constructor.
     *
     * @param RequestStack $requestStack
     * @param TokenStorageInterface $tokenStorage
     * @param DomaineRepository $domainRepository
     */
    public function __construct(
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage,
        DomaineRepository $domainRepository
    ) {
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
        $this->domainRepository = $domainRepository;
    }

    /**
     * @param DomainInterface $item
     *
     * @return Domaine
     */
    public function getItemDomain(DomainInterface $item)
    {
        $currentDomainId = $this->requestStack->getCurrentRequest()->getSession()->get('domaineId');

        foreach ($item->getDomains() as $domain) {
            if ($domain->getId() === $currentDomainId) {
                return $domain;
            }
        }

        /** @var User $currentUser */
        $currentUser = $this->tokenStorage->getToken()->getUser();

        foreach ($item->getDomains() as $domain) {
            if ($currentUser->getDomaines()->contains($domain)) {
                return $domain;
            }
        }

        if ($item->getDomains() !== null && $item->getDomains()->first() !== false) {
            return $item->getDomains()->first();
        }

        return $this->domainRepository->find($currentDomainId);
    }
}
