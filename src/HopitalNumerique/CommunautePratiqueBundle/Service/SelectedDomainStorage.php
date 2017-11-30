<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service;

use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\DomaineBundle\Repository\DomaineRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SelectedDomainStorage
{
    const SESSION_KEY = 'cdp_selected_domain';

    const ALL_DOMAINS_KEYWORD = 'all';

    /**
     * @var SessionInterface $session
     */
    protected $session;

    /**
     * @var DomaineRepository $domainRepository
     */
    protected $domainRepository;

    /**
     * @var CurrentDomaine $currentDomain
     */
    protected $currentDomain;

    /**
     * @var AvailableDomainsRetriever $domainAvailableRetriever
     */
    protected $domainAvailableRetriever;

    /**
     * SelectedDomainStorage constructor.
     *
     * @param SessionInterface $session
     * @param DomaineRepository $domainRepository
     * @param CurrentDomaine $currentDomain
     * @param AvailableDomainsRetriever $domainAvailableRetriever
     */
    public function __construct(
        SessionInterface $session,
        DomaineRepository $domainRepository,
        CurrentDomaine $currentDomain,
        AvailableDomainsRetriever $domainAvailableRetriever
    ) {
        $this->session = $session;
        $this->domainRepository = $domainRepository;
        $this->currentDomain = $currentDomain;
        $this->domainAvailableRetriever = $domainAvailableRetriever;
    }

    /**
     * Retrieve selected domain from storage
     *
     * @return null|object|Domaine
     */
    public function getSelectedDomain()
    {
        if ($this->session->has(self::SESSION_KEY)) {

            $domainId = $this->session->get(self::SESSION_KEY);

            if ($domainId === self::ALL_DOMAINS_KEYWORD) {
                return null;
            } elseif (
                ($domain = $this->domainRepository->find($domainId)) &&
                in_array($domain, $this->domainAvailableRetriever->getAvailableDomains())
            ) {
                return $domain;
            }

            $this->eraseSelectedDomain();
        }

        return $this->currentDomain->get();
    }

    /**
     * Set selected domain to storage
     *
     * @param Domaine $domain
     */
    public function setSelectedDomain(Domaine $domain = null)
    {
        if (null === $domain) {
            $this->session->set(self::SESSION_KEY, self::ALL_DOMAINS_KEYWORD);
        } else {
            $this->session->set(self::SESSION_KEY, $domain->getId());
        }
    }

    /**
     * Remove selected domain from storage.
     */
    public function eraseSelectedDomain()
    {
        $this->session->remove(self::SESSION_KEY);
    }
}
