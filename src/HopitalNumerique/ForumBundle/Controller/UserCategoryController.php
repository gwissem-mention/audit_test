<?php
namespace HopitalNumerique\ForumBundle\Controller;

use CCDNForum\ForumBundle\Controller\UserCategoryController as UserCategoryControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class UserCategoryController extends UserCategoryControllerBase
{
    use ForumControllerAuthorizationCheckerTrait;

    /**
     * Recalcule les derniers messages.
     *
     * @param $token
     *
     * @return Response
     */
    public function recalculateLastMessagesAction($token)
    {
        if ('jkfghdsfhfbgdhfsbgdfvbdkjfg' != $token) {
            return new Response('Traitement non autorisé.');
        }

        $this->container->get('hopitalnumerique_forum.manager.board')->recalculateAllLastMessages();

        return new Response('Derniers messages recalculés.');
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
