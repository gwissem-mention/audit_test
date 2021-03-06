<?php

namespace Nodevo\MenuBundle\Provider;

use Knp\Menu\FactoryInterface;
use Knp\Menu\NodeInterface;
use Knp\Menu\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * NodeLoader aware of security context.
 */
class NodeLoader implements LoaderInterface
{
    private $factory;
    private $security;
    private $container;
    private $class = '';

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * NodeLoader constructor.
     *
     * @param FactoryInterface $factory
     * @param SecurityContextInterface $security
     * @param ContainerInterface $container
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        FactoryInterface $factory,
        SecurityContextInterface $security,
        ContainerInterface $container,
        TokenStorageInterface $tokenStorage
    ) {
        $this->factory = $factory;
        $this->security = $security;
        $this->container = $container;
        $this->tokenStorage = $tokenStorage;
    }

    public function load($data)
    {
        if (!$data instanceof NodeInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unsupported data. Expected Knp\Menu\NodeInterface but got ',
                    is_object($data) ? get_class($data) : gettype($data)
                )
            );
        }

        $item = $this->factory->createItem($data->getName(), $data->getOptions());
        $uri = $item->getUri();

        //get security ACL
        $aclManager = $this->container->get('nodevo_acl.manager.acl');
        $securityResult = !is_null($uri) && $uri != 'javascript:;' && 0 !== strpos($uri, 'http://')
            ? $aclManager->checkAuthorization($uri, $this->security->getToken()->getUser())
            : null;

        if ($securityResult == VoterInterface::ACCESS_DENIED) {
            return null;
        }

        //parcours des enfants
        $haveChilds = false;
        foreach ($data->getChildren() as $childNode) {
            if (null !== $this->tokenStorage->getToken() && $this->security->isGranted($childNode->getRole())) {
                if (!is_null($element = $this->load($childNode))) {
                    //set class for childrens ( if  set )
                    $element->setChildrenAttribute('class', $this->class);

                    //add elements
                    $item->addChild($element);
                    $haveChilds = true;
                }
            }
        }

        //système qui permet de cacher les enfants si ils ne sont pas accessible par l'user connecté
        if ($uri == 'javascript:;' && !$haveChilds) {
            return null;
        }

        return $item;
    }

    public function supports($data)
    {
        return $data instanceof NodeInterface;
    }

    /**
     * Set class.
     *
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }
}
