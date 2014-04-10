<?php

namespace Nodevo\MenuBundle\Provider;

use Knp\Menu\FactoryInterface;
use Knp\Menu\NodeInterface;
use Knp\Menu\Loader\LoaderInterface;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * NodeLoader aware of security context
 */
class NodeLoader implements LoaderInterface
{
    private $factory;
    private $security;
    private $container;
    private $class = '';

    public function __construct(FactoryInterface $factory, SecurityContextInterface $security, $container )
    {
        $this->factory   = $factory;
        $this->security  = $security;
        $this->container = $container;
    }

    public function load($data)
    {
        if (!$data instanceof NodeInterface)
            throw new \InvalidArgumentException(sprintf('Unsupported data. Expected Knp\Menu\NodeInterface but got ', is_object($data) ? get_class($data) : gettype($data)));

        $item = $this->factory->createItem($data->getName(), $data->getOptions());
        $uri  = $item->getUri();

        //get security ACL
        $securityResult = !is_null($uri) && $uri != 'javascript:;' ? $this->container->get('nodevo_acl.manager.acl')->checkAuthorization( $uri , $this->security->getToken()->getUser() ) : null; 
        if( $securityResult == VoterInterface::ACCESS_DENIED )
            return null;

        //parcours des enfants
        $haveChilds = false;
        foreach ($data->getChildren() as $childNode) {
            if ( $this->security->isGranted( $childNode->getRole() ) ){
                if( !is_null($element = $this->load($childNode)) ){
                    //set class for childrens ( if  set )
                    $element->setChildrenAttribute('class', $this->class );

                    //add elements
                    $item->addChild( $element );
                    $haveChilds = true;
                }
            }
        }

        //système qui permet de cacher les enfants si ils ne sont pas accessible par l'user connecté
        if( $uri == 'javascript:;' && !$haveChilds )
            return null;

        return $item;   
    }

    public function supports($data)
    {
        return $data instanceof NodeInterface;
    }

    /**
     * Set class
     *
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }
}