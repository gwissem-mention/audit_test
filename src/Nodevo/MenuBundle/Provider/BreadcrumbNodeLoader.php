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
    private $_factory;
    private $_security;
    private $_container;
    private $_rootNode;

    /**
     * [__construct description]
     *
     * @param FactoryInterface         $factory   [description]
     * @param SecurityContextInterface $security  [description]
     * @param [type]                   $container [description]
     * @param array                    $options   [description]
     */
    public function __construct( FactoryInterface $factory, SecurityContextInterface $security, $container, $options = array() )
    {
        $this->_factory   = $factory;
        $this->_security  = $security;
        $this->_container = $container;
        $this->_rootNode  = isset($options['breadcrumbRoot']) ? $options['breadcrumbRoot'] : false;
    }

    /**
     * Charge le fil d'ariane
     *
     * @param array $data Liste des éléments
     *
     * @return MenuItem
     */
    public function load($data)
    {
        //build Menu
        $menu = $this->_factory->createItem('root');

        if($this->_rootNode)
            $menu->addChild('Accueil', array('route' => $this->_rootNode ) );

        //récupère l'arborescence
        $nodesArray = $this->getDatas($data)->getBreadcrumbsArray();
        $nodesArray = $nodesArray[0]['item'];

        //get children
        $childs = array_values($nodesArray->getChildren());
        $menu = $this->addChild( $menu, $childs );

        return $menu;
    }

    /**
     * Récupère l'arbre du Menu
     *
     * @param array $data Liste des éléments
     *
     * @return items
     */
    public function getDatas($data)
    {
        if (!$data instanceof NodeInterface)
            throw new \InvalidArgumentException(sprintf('Unsupported data. Expected Knp\Menu\NodeInterface but got ', is_object($data) ? get_class($data) : gettype($data)));

        $item = $this->_factory->createItem($data->getName(), $data->getOptions());

        foreach ($data->getChildren() as $childNode)
        {
            if( !is_null($element = $this->getDatas($childNode)) )
            {
                $options = $childNode->getOptions();
                if ($options['route'] === $this->_container->get('request')->attributes->get('_route') || $element->getChildren())
                    $item->addChild( $element );
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
     * Ajoute l'arbo des enfants du fil d'ariane
     *
     * @param MenutItem $menu   Le menu
     * @param array     $childs Tableau des enfants
     */
    private function addChild( $menu, $childs )
    {
        if( isset($childs[0]) ){
            $element   = $childs[0];
            $newChilds = array_values($element->getChildren());
            $options   = array();

            //remove javascript link
            if( $element->getUri() != 'javascript:;' )
                $options['uri'] = $element->getUri();
            
            //Test if childs present
            if( count($newChilds) > 0 )
            {
                $menu->addChild( $element->getLabel(), $options );
                $menu = $this->addChild( $menu, $newChilds );    
            }else{
                //No Link on Last Element
                $menu->addChild( $element->getLabel() );
            }
        }
        
        return $menu;
    }
}