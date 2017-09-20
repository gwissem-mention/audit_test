<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service;

use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\DomaineBundle\Repository\DomaineRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SelectedDomainStorage
{
    const SESSION_KEY = 'cdp_selected_domain';
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
     * SelectedDomainStorage constructor.
     *
     * @param SessionInterface $session
     * @param DomaineRepository $domainRepository
     * @param CurrentDomaine $currentDomain
     */
    public function __construct(SessionInterface $session, DomaineRepository $domainRepository, CurrentDomaine $currentDomain)
    {
        $this->session = $session;
        $this->domainRepository = $domainRepository;
        $this->currentDomain = $currentDomain;
    }

    /**
     * Retrieve selected domain from storage
     *
     * @return null|object|Domaine
     */
    public function getSelectedDomain()
    {
        if (!$this->session->has(self::SESSION_KEY)) {
            return $this->currentDomain->get();
        }

        return $this->domainRepository->find($this->session->get(self::SESSION_KEY)) ?: null;
    }

    /**
     * Set selected domain to storage
     *
     * @param Domaine $domain
     */
    public function setSelectedDomain(Domaine $domain = null)
    {
        if (null === $domain) {
            $this->session->set(self::SESSION_KEY, 'all');
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
