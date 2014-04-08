<?php

namespace Nodevo\MenuBundle\Provider;

use Knp\Menu\FactoryInterface;
use Knp\Menu\NodeInterface;
use Knp\Menu\Loader\LoaderInterface;

use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * BreadcrumbNodeLoader
 */
class BreadcrumbNodeLoader implements LoaderInterface
{
    private $factory;
    private $security;
    private $container;
    private $_rootNode;

    /**
     * [__construct description]
     *
     * @param FactoryInterface         $factory   [description]
     * @param SecurityContextInterface $security  [description]
     * @param [type]                   $container [description]
     * @param array                    $options   [description]
     */
    public function __construct(FactoryInterface $factory, SecurityContextInterface $security, $container, $options = array())
    {
        $this->factory   = $factory;
        $this->security  = $security;
        $this->container = $container;
        $this->_rootNode = isset($options['rootNode']) ? $options['breadcrumbRouteRacine'] : '';
    }

    /**
     * [getMenu description]
     *
     * @param  [type] $data [description]
     *
     * @return [type]
     */
    public function getMenu($data)
    {
        $nodes = $this->load($data);

        $menu = clone $nodes;
        $menu->setChildren(array());

        $options          = $data->getOptions();
        $options['route'] = $this->_rootNode;

        $item = $this->factory->createItem($data->getName(), $options);
        $item->setName('Accueil');
        $item->setLabel('Accueil');

        $menu->addChild($item);
        
        $this->addChildren($menu, $nodes);

        return $menu;
    }

    /**
     * [load description]
     *
     * @param  [type] $data [description]
     *
     * @return [type]
     */
    public function load($data)
    {
        if (!$data instanceof NodeInterface)
            throw new \InvalidArgumentException(sprintf('Unsupported data. Expected Knp\Menu\NodeInterface but got ', is_object($data) ? get_class($data) : gettype($data)));

        $item = $this->factory->createItem($data->getName(), $data->getOptions());

        foreach ($data->getChildren() as $childNode) {

            if( !is_null($element = $this->load($childNode)) ){
                $options = $childNode->getOptions();
                if ($options['route'] === $this->container->get('request')->attributes->get('_route') || $element->getChildren())
                {                    
                    $item->addChild( $element ); 
                }
            }
        }

        return $item;   
    }

    /**
     * [supports description]
     *
     * @param  [type] $data [description]
     *
     * @return [type]
     */
    public function supports($data)
    {
        return $data instanceof NodeInterface;
    }

    /**
     * [addChildren description]
     *
     * @param [type] $menu  [description]
     * @param [type] $nodes [description]
     */
    private function addChildren($menu, $nodes)
    {
        foreach ($nodes->getChildren() as $childNode) 
        {            
            if ($childNode->getUri() !== 'javascript:;')
            {                
                $child = clone $childNode;
                $child->setChildren(array());
                $child->setParent(null);
                $menu->addChild($child);
            }

            $this->addChildren($menu, $nodes->getChild($childNode->getName()));
        }
    }
}