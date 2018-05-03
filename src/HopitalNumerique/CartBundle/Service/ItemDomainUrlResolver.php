<?php

namespace HopitalNumerique\CartBundle\Service;

use HopitalNumerique\CartBundle\Model\Item\Item;
use Symfony\Component\Routing\RouterInterface;

/**
 * Allows to build URL link for an Item object.
 */
class ItemDomainUrlResolver
{
    /**
     * @var ItemDomainResolver $itemDomainResolver
     */
    protected $itemDomainResolver;

    /**
     * @var RouterInterface $router
     */
    protected $router;

    /**
     * ItemDomainUrlResolver constructor.
     *
     * @param ItemDomainResolver $itemDomainResolver
     * @param RouterInterface $router
     */
    public function __construct(
        ItemDomainResolver $itemDomainResolver,
        RouterInterface $router
    ) {
        $this->itemDomainResolver = $itemDomainResolver;
        $this->router = $router;
    }

    /**
     * @param Item $item
     *
     * @return string
     */
    public function getItemUrl(Item $item)
    {
        $domain = $this->itemDomainResolver->getItemDomain($item);

        $fragment = null;
        if (!is_null($item->getUriFragment())) {
            $fragment = '#'.$item->getUriFragment();
        }

        return sprintf('%s%s%s', $domain->getUrl(), $this->router->generate($item->getRoute(), $item->getRouteParameters()), $fragment);
    }
}
