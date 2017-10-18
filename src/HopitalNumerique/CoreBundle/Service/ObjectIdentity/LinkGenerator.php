<?php

namespace HopitalNumerique\CoreBundle\Service\ObjectIdentity;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\RouterInterface;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;

class LinkGenerator
{
    /**
     * @var RouterInterface $router
     */
    protected $router;

    /**
     * @var array
     */
    protected $objectIdentityRoutingConfiguration;

    /**
     * LinkGenerator constructor.
     *
     * @param RouterInterface $router
     * @param array $objectIdentityRoutingConfiguration
     */
    public function __construct(RouterInterface $router, array $objectIdentityRoutingConfiguration)
    {
        $this->router = $router;
        $this->objectIdentityRoutingConfiguration = $objectIdentityRoutingConfiguration;
    }

    /**
     * @param ObjectIdentity $objectIdentity
     * @param string $type
     *
     * @return string
     * @throws \Exception
     */
    public function getLink(ObjectIdentity $objectIdentity, $type)
    {
        if (!isset($this->objectIdentityRoutingConfiguration[$type])) {
            throw new \Exception(sprintf('Object identity routing type {%s} not founded', $type));
        }

        if (!isset($this->objectIdentityRoutingConfiguration[$type][$objectIdentity->getClass()])) {
            throw new \Exception(sprintf(
                'Object identity routing class {%s} not found in type {%s}',
                $objectIdentity->getClass(),
                $type
            ));
        }

        $routeConfiguration = $this->objectIdentityRoutingConfiguration[$type][$objectIdentity->getClass()];

        $propertyAccessor = new PropertyAccessor();

        $parameters = [];
        if (isset($routeConfiguration['parameters'])) {
            foreach ($routeConfiguration['parameters'] as $key => $parameter) {
                $parameters[$key] = $propertyAccessor->getValue($objectIdentity, $parameter);
            }
        }

        return $this->router->generate($routeConfiguration['route'], $parameters);
    }
}
