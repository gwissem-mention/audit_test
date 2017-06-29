<?php

namespace HopitalNumerique\CartBundle\Twig;

use HopitalNumerique\DomaineBundle\Repository\DomaineRepository;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use HopitalNumerique\CartBundle\Model\Item\Item;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CartExtension extends \Twig_Extension
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
     * @var RouterInterface $router
     */
    protected $router;

    /**
     * @var DomaineRepository $domainRepository
     */
    protected $domainRepository;

    /**
     * CartExtension constructor.
     *
     * @param RequestStack $requestStack
     * @param TokenStorageInterface $tokenStorage
     * @param RouterInterface $router
     * @param DomaineRepository $domaineRepository
     */
    public function __construct(RequestStack $requestStack, TokenStorageInterface $tokenStorage, RouterInterface $router, DomaineRepository $domaineRepository)
    {
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->domainRepository = $domaineRepository;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getItemUrl', array($this, 'getItemUrl')),
        );
    }

    /**
     * @param Item $item
     *
     * @return string
     */
    public function getItemUrl(Item $item)
    {
        $currentDomainId = $this->requestStack->getCurrentRequest()->getSession()->get('domaineId');

        foreach ($item->getDomains() as $domain) {
            if ($domain->getId() === $currentDomainId) {
                return $this->buildUrl($item, $domain->getUrl());
            }
        }

        /** @var User $currentUser */
        $currentUser = $this->tokenStorage->getToken()->getUser();

        foreach ($item->getDomains() as $domain) {
            if ($currentUser->getDomaines()->contains($domain)) {
                return $this->buildUrl($item, $domain->getUrl());
            }
        }

        if ($item->getDomains()->first() !== false) {
            return $this->buildUrl($item, $item->getDomains()->first()->getUrl());
        }

        return $this->buildUrl($item, $this->domainRepository->find($currentDomainId)->getUrl());
    }

    /**
     * @param Item $item
     * @param string $baseUrl
     *
     * @return string
     */
    private function buildUrl(Item $item, $baseUrl)
    {
        $fragment = null;
        if (!is_null($item->getUriFragment())) {
            $fragment = '#'.$item->getUriFragment();
        }

        return sprintf('%s%s%s', $baseUrl, $this->router->generate($item->getRoute(), $item->getRouteParameters()), $fragment);
    }

    /**
     * Retourne le nom de l'extension : utilisé dans les services.
     *
     * @return string
     */
    public function getName()
    {
        return 'cart_extension';
    }
}
