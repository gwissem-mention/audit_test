<?php
namespace HopitalNumerique\ForumBundle\Controller;

use CCDNForum\ForumBundle\Controller\UserCategoryController as UserCategoryControllerBase;
use Symfony\Component\HttpFoundation\Response;

class UserCategoryController extends UserCategoryControllerBase
{
    /**
     * Recalcule les derniers messages.
     */
    public function recalculateLastMessagesAction($token)
    {
        if ('jkfghdsfhfbgdhfsbgdfvbdkjfg' != $token)
            return new Response('Traitement non autorisé.');
            
        $this->container->get('hopitalnumerique_forum.manager.board')->recalculateAllLastMessages();
        
        return new Response('Derniers messages recalculés.');
    }
}
