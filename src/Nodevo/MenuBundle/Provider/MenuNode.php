<?php

namespace Nodevo\MenuBundle\Provider;

use Knp\Menu\NodeInterface;

class MenuNode implements NodeInterface
{
    protected $_name      = null;
    protected $_options   = array();
    protected $_childrens = array();
    protected $_entity    = null;
    
    public $id;
    public $role = null;

    public function __construct( $item = null )
    {
        if ( null !== $item ) {
            $this->setName( $item->getName() );
            $this->setOptions( $item );

            $this->id   = $item->getId();
            $this->role = $item->getRole();
        }
    }

    public function setName( $name )
    {
        $this->_name = $name;
    }

    public function setOptions( $item )
    {
        $routeParametres = null;
        if ($item->getRouteParameters() != null)
            $routeParametres = json_decode($item->getRouteParameters(), true);
        
        $this->_options = array(
            'route'              => $item->getRoute(),
            'routeParameters'    => $routeParametres,                     //Besoin d'un tableau associatif
            'routeAbsolute'      => $item->getRouteAbsolute(),
            'uri'                => $item->getUri(),
            'label'              => null,
            'attributes'         => array( 'icon' => $item->getIcon() ),  // attributs html des balises
            'linkAttributes'     => array(),                              // attributs html des balises
            'childrenAttributes' => array(),                              // attributs html des balises
            'labelAttributes'    => array(),                              // attributs html des balises
            'extras'             => array(),
            'current'            => null,
            'display'            => $item->getDisplay(),
            'displayChildren'    => $item->getDisplayChildren()
        );
    }

    public function setChildrens( $childs )
    {
        $this->_childrens = $childs;
    }

    public function addChildren( MenuNode $child )
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