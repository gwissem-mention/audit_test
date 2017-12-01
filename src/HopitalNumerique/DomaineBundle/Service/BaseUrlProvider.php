<?php

namespace HopitalNumerique\DomaineBundle\Service;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\DomaineBundle\Twig\DomaineExtension;

/**
 * Class BaseUrlProvider
 *
 * Retrieves the base url based on a list of domains of an entity (or anything) and a list of selected domains
 */
class BaseUrlProvider
{
    /**
     * @var Domaine
     */
    protected $currentDomain;

    /**
     * BaseUrlProvider constructor.
     *
     * @param DomaineExtension $domaineExtension
     */
    public function __construct(DomaineExtension $domaineExtension)
    {
        $this->currentDomain = $domaineExtension->getDomaineCurrent();
    }

    /**
     * @param Domaine[] $entityDomains
     * @param Domaine[] $selectedDomains
     *
     * @return string
     */
    public function getBaseUrl($entityDomains = [], $selectedDomains = [])
    {
        // Returns the current domain base url if it is in the entity domains list and in the selected domains list
        if (null !== $this->currentDomain) {
            foreach ($entityDomains as $entityDomain) {
                foreach ($selectedDomains as $selectedDomain) {
                    if ($entityDomain->getId() === $selectedDomain->getId()) {
                        if ($entityDomain->getId() === $this->currentDomain->getId()) {
                            return $entityDomain->getUrl();
                        }
                    }
                }
            }
        }

        // Returns the first domain base url that is both in the entity domains list and the selected domains list
        foreach ($entityDomains as $entityDomain) {
            foreach ($selectedDomains as $selectedDomain) {
                if ($entityDomain->getId() === $selectedDomain->getId()) {
                    return $entityDomain->getUrl();
                }
            }
        }

        // Returns the domain base url of the current domain if it is in the entity domains list
        if (null !== $this->currentDomain) {
            foreach ($entityDomains as $entityDomain) {
                if ($entityDomain->getId() === $this->currentDomain->getId()) {
                    return $entityDomain->getUrl();
                }
            }
        }

        // Returns the first domain base url of the entity domains list
        if (count($entityDomains) > 0) {
            return current($entityDomains)->getUrl();
        }

        // Returns the current domain base url
        return null !== $this->currentDomain ? $this->currentDomain->getUrl() : '';
    }
}
