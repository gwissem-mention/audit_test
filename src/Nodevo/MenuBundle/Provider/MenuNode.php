<?php

namespace Nodevo\MenuBundle\Provider;

use Knp\Menu\NodeInterface;

class MenuNode implements NodeInterface
{
    protected $_name = null;
    protected $_options = [];
    protected $_childrens = [];
    protected $_entity = null;

    public $id;
    public $role = null;

    public function __construct($item = null)
    {
        if (null !== $item) {
            $this->setName('menu-' . $item->getId());
            $this->setOptions($item);

            $this->id = $item->getId();
            $this->role = $item->getRole();
        }
    }

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function setOptions($item)
    {
        $routeParameters = null;
        if ($item->getRouteParameters() != null) {
            $routeParameters = json_decode($item->getRouteParameters(), true);
        }

        $this->_options = [
            'route' => $item->getRoute(),
            'routeParameters' => $routeParameters,                     //Besoin d'un tableau associatif
            'routeAbsolute' => $item->getRouteAbsolute(),
            'uri' => $item->getUri(),
            'label' => $item->getName(),
            'attributes' => ['icon' => $item->getIcon()],  // attributs html des balises
            'linkAttributes' => [],                              // attributs html des balises
            'childrenAttributes' => [],                              // attributs html des balises
            'labelAttributes' => [],                              // attributs html des balises
            'extras' => [],
            'current' => null,
            'display' => $item->getDisplay(),
            'displayChildren' => $item->getDisplayChildren(),
        ];
    }

    public function setChildrens($childs)
    {
        $this->_childrens = $childs;
    }

    public function addChildren(MenuNode $child)
    {
        $this->_childrens[] = $child;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function getChildren()
    {
        return $this->_childrens;
    }

    public function getRole()
    {
        return $this->role;
    }
}
