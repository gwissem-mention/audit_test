<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Twig;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\CommunautePratiqueBundle\Service\SelectedDomainStorage;
use HopitalNumerique\CommunautePratiqueBundle\Service\AvailableDomainsRetriever;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SelectedDomainSelectorExtension extends \Twig_Extension
{
    /**
     * @var \Twig_Environment $twig
     */
    protected $twig;

    /**
     * @var SelectedDomainStorage $selectedDomainStorage
     */
    protected $selectedDomainStorage;

    /**
     * @var AuthorizationCheckerInterface $authorizationChecker
     */
    protected $authorizationChecker;

    /**
     * @var AvailableDomainsRetriever $domainAvailableRetriever
     */
    protected $domainAvailableRetriever;

    /**
     * SelectedDomainSelectorExtension constructor.
     *
     * @param \Twig_Environment $twig
     * @param SelectedDomainStorage $selectedDomainStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param AvailableDomainsRetriever $domainAvailableRetriever
     */
    public function __construct(
        \Twig_Environment $twig,
        SelectedDomainStorage $selectedDomainStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        AvailableDomainsRetriever $domainAvailableRetriever
    ) {
        $this->twig = $twig;
        $this->selectedDomainStorage = $selectedDomainStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->domainAvailableRetriever = $domainAvailableRetriever;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('selectedDomainSelector', [$this, 'getSelectedDomainSelector'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @return string
     */
    public function getSelectedDomainSelector()
    {
        if (!$this->authorizationChecker->isGranted('ROLE_USER')) {
            return null;
        }

        $selectedDomain = $this->selectedDomainStorage->getSelectedDomain();

        return $this->twig->render('HopitalNumeriqueCommunautePratiqueBundle::selected_domain_selector.html.twig', [
            'selectedDomain' => $selectedDomain,
            'availableDomains' => $this->domainAvailableRetriever->getAvailableDomains(),
        ]);
    }
}
