<?php

namespace HopitalNumerique\ForumBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ModeratorPostController extends \CCDNForum\ForumBundle\Controller\ModeratorPostController
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
