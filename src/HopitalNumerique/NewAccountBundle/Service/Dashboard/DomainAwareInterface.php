<?php

namespace HopitalNumerique\NewAccountBundle\Service\Dashboard;

use HopitalNumerique\DomaineBundle\Entity\Domaine;

/**
 * Interface DomainAwareInterface
 */
interface DomainAwareInterface
{
    /**
     * @param Domaine[] $domains
     */
    public function setDomains($domains = null);
}
