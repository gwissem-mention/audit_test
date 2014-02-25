<?php

namespace Nodevo\MenuBundle\Provider;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class MenuProvider implements MenuProviderInterface
{
    /**
     * @var FactoryInterface
     */
    protected $loader           = null;
    protected $breadcrumbLoader = null;
    protected $menuManager      = null;
    protected $itemManager      = null;
    protected $_menuEntity      = null;

    public function __construct($loader, $breadcrumbLoader, $menuManager, $itemManager)
    {
        $this->loader           = $loader;
        $this->breadcrumbLoader = $breadcrumbLoader;
        $this->menuManager      = $menuManager;
        $this->itemManager      = $itemManager;
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

        if (isset($options['breadcrumb']) && $options['breadcrumb'] === 'yes')
        {
            try {
                $menu = $this->breadcrumbLoader->getMenu($tree);
            } 
            catch (\Exception $e) {
                $tree   = $this->menuManager->getTree( $this->_menuEntity, true);
                $menu   = $this->breadcrumbLoader->getMenu($tree);
            }
            
            if ( !is_null($this->_menuEntity->getCssClass()) )
                $menu->setChildrenAttribute('class', 'breadcrumb' );
        }
        else
        {
            //Si l'arbre ne peut pas se générer correctement alors on le force la regénération
            try {
                $menu = $this->loader->load($tree);
            } 
            catch (\Exception $e) {
                $tree   = $this->menuManager->getTree( $this->_menuEntity, true);
                $menu   = $this->loader->load($tree);
            }

            if ( !is_null($this->_menuEntity->getCssClass()) )
                $menu->setChildrenAttribute('class', $this->_menuEntity->getCssClass() );

            if ( !is_null($this->_menuEntity->getCssId()) )
                $menu->setChildrenAttribute('id', $this->_menuEntity->getCssId() );
        }
        
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