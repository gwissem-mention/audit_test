<?php

namespace Nodevo\MenuBundle\Provider;

use Knp\Menu\FactoryInterface;
use Knp\Menu\Provider\MenuProviderInterface;

class MenuProvider implements MenuProviderInterface
{
    /**
     * @var FactoryInterface
     */
    protected $loader           = null;
    protected $breadcrumbLoader = null;
    protected $menuManager      = null;
    protected $_menuEntity      = null;

    public function __construct($loader, $breadcrumbLoader, $menuManager)
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
        $type = (isset($options['breadcrumb']) && $options['breadcrumb'] === 'yes') ? 'breadcrumb' : 'menu';

        //Cas menu
        if( $type == 'menu' ) {
            $class = !is_null($this->_menuEntity->getCssClass()) ? $this->_menuEntity->getCssClass() : '';
            $id    = !is_null($this->_menuEntity->getCssId()) ? $this->_menuEntity->getCssId() : '';

            //set Class for childrens
            $this->loader->setClass($class);

        //Cas fil d'ariane
        }else{
            $class = 'breadcrumb';
            $id    = '';
        }

        //Get Menu
        try {
            $menu = $type == 'menu' ? $this->loader->load($tree) : $this->breadcrumbLoader->getMenu($tree);
        } 
        catch (\Exception $e) {
            $tree = $this->menuManager->getTree( $this->_menuEntity, true);
            $menu = $type == 'menu' ? $this->loader->load($tree) : $this->breadcrumbLoader->getMenu($tree);
        }
        
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