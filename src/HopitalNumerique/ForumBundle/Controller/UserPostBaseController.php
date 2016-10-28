<?php

namespace HopitalNumerique\ForumBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

class UserPostBaseController extends \CCDNForum\ForumBundle\Controller\UserPostBaseController
{
    use ForumControllerAuthorizationCheckerTrait;

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
