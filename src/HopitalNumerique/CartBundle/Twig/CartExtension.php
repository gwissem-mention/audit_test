<?php

namespace HopitalNumerique\CartBundle\Twig;

use HopitalNumerique\CartBundle\Model\Item\Item;
use HopitalNumerique\CartBundle\Service\ItemDomainResolver;
use HopitalNumerique\CartBundle\Service\ItemDomainUrlResolver;

class CartExtension extends \Twig_Extension
{
    /**
     * @var ItemDomainResolver $itemDomainResolver
     */
    protected $itemDomainResolver;

    /**
     * @var ItemDomainUrlResolver $itemDomainUrlResolver
     */
    protected $itemDomainUrlResolver;

    /**
     * CartExtension constructor.
     *
     * @param ItemDomainResolver $itemDomainResolver
     */
    public function __construct(ItemDomainResolver $itemDomainResolver, ItemDomainUrlResolver $itemDomainUrlResolver)
    {
        $this->itemDomainResolver = $itemDomainResolver;
        $this->itemDomainUrlResolver = $itemDomainUrlResolver;
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
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('fixDomainLink', array($this, 'fixDomainLink')),
        );
    }

    /**
     * @param Item $item
     *
     * @return string
     */
    public function getItemUrl(Item $item)
    {
        return $this->itemDomainUrlResolver->getItemUrl($item);
    }

    /**
     * @param $text
     * @param $item
     *
     * @return null|string|string[]
     */
    public function fixDomainLink($text, $item)
    {
        $domainUrl = $this->itemDomainResolver->getItemDomain($item)->getUrl();
        return preg_replace('/href="\//', 'href="' . $domainUrl . '/', $text);
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
