<?php

namespace Nodevo\MenuBundle\Provider;

use Knp\Menu\FactoryInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Nodevo\MenuBundle\Manager\MenuManager;
use Nodevo\MenuBundle\Provider\BreadcrumbNodeLoader;
use Nodevo\MenuBundle\Provider\NodeLoader;

class MenuProvider implements MenuProviderInterface
{
    /**
     * @var FactoryInterface
     */
    protected $loader           = null;
    protected $breadcrumbLoader = null;
    protected $menuManager      = null;
    protected $_menuEntity      = null;

    /**
     * [__construct description]
     *
     * @param NodeLoader           $loader           [description]
     * @param BreadcrumbNodeLoader $breadcrumbLoader [description]
     * @param MenuManager          $menuManager      [description]
     */
    public function __construct(NodeLoader $loader, BreadcrumbNodeLoader $breadcrumbLoader, MenuManager $menuManager)
    {
        $this->loader           = $loader;
        $this->breadcrumbLoader = $breadcrumbLoader;
        $this->menuManager      = $menuManager;
    }

    /**
     * Looks for a menu with the bundle:class:method format
     *
     * For example, AcmeBundle:Builder:mainMenu would create and instantiate
     * an Acme\DemoBundle\Menu\Builder class and call the mainMenu() method
     * on it. The method is passed the menu factory.
     *
     * @param string $name    The alias name of the menu
     * @param array  $options
     *
     * @return \Knp\Menu\ItemInterface
     * @throws \InvalidArgumentException
     */
    public function get($name, array $options = array())
    {
        $tree = $this->menuManager->getTree( $this->_menuEntity );

        //Cas breadcrumb
        if( isset($options['breadcrumb']) && $options['breadcrumb'] === 'yes' )
        {
            $loader = $this->breadcrumbLoader;
            $class  = 'breadcrumb';
            $id     = '';
        }
        //Cas Menu
        else
        {
            $class  = !is_null($this->_menuEntity->getCssClass()) ? $this->_menuEntity->getCssClass() : '';
            $id     = !is_null($this->_menuEntity->getCssId())    ? $this->_menuEntity->getCssId()    : '';
            $loader = $this->loader;

            //set Class for childrens
            $loader->setClass($class);
        }

        //Get Menu
        //try {
            $menu = $loader->load($tree);
        // } 
        // catch (\Exception $e) {
        //     $tree = $this->menuManager->getTree( $this->_menuEntity, true);
        //     $menu = $loader->load($tree);
        // }
        
        //set Vars    
        $menu->setChildrenAttribute('class', $class );
        $menu->setChildrenAttribute('id', $id );
        
        return $menu;
    }

    /**
     * Verifies if the given name follows the bundle:class:method alias syntax.
     *
     * @param string $name    The alias name of the menu
     * @param array  $options
     *
     * @return Boolean
     */
    public function has($name, array $options = array())
    {
        $this->_menuEntity = $this->menuManager->findOneByAlias( $name );

        return $this->_menuEntity !== null;
    }
}