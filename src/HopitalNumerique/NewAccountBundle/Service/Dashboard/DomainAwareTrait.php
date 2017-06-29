<?php

namespace HopitalNumerique\NewAccountBundle\Service\Dashboard;

use HopitalNumerique\DomaineBundle\Entity\Domaine;

/**
 * Trait DomainAwareTrait
 */
trait DomainAwareTrait
{
    /**
     * @var Domaine[]
     */
    protected $domains;

    /**
     * @param Domaine[] $domains
     */
    public function setDomains($domains = null)
    {
        $this->domains = $domains;
    }
}
