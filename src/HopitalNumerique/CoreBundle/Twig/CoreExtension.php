<?php

namespace HopitalNumerique\CoreBundle\Twig;

use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\CoreBundle\Service\ObjectIdentity\LinkGenerator;

class CoreExtension extends \Twig_Extension
{
    /**
     * @var LinkGenerator
     */
    protected $linkGenerator;

    /**
     * CoreExtension constructor.
     *
     * @param LinkGenerator $linkGenerator
     */
    public function __construct(LinkGenerator $linkGenerator)
    {
        $this->linkGenerator = $linkGenerator;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('objectIdentityLink', [$this, 'getObjectIdentityLink']),
        ];
    }

    /**
     * @param ObjectIdentity $objectIdentity
     * @param string $type
     *
     * @return string
     */
    public function getObjectIdentityLink(ObjectIdentity $objectIdentity, $type)
    {
        return $this->linkGenerator->getLink($objectIdentity, $type);
    }
}
