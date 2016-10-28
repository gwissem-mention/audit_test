<?php

namespace HopitalNumerique\ForumBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ModeratorTopicBaseController extends \CCDNForum\ForumBundle\Controller\ModeratorTopicBaseController
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
