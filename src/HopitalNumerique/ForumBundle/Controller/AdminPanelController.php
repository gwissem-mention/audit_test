<?php

namespace HopitalNumerique\ForumBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

class AdminPanelController extends \CCDNForum\ForumBundle\Controller\AdminPanelController
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
