<?php

namespace HopitalNumerique\ForumBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

class AdminCategoryBaseController extends \CCDNForum\ForumBundle\Controller\AdminCategoryBaseController
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
