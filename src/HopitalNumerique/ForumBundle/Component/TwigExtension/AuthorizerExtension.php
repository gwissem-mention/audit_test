<?php
namespace HopitalNumerique\ForumBundle\Component\TwigExtension;

use CCDNForum\ForumBundle\Component\TwigExtension\AuthorizerExtension as AuthorizerExtensionBase;
use CCDNForum\ForumBundle\Entity\Forum;
use CCDNForum\ForumBundle\Entity\Board;
use CCDNForum\ForumBundle\Entity\Subscription;

class AuthorizerExtension extends AuthorizerExtensionBase
{
    public function getFunctions()
    {
        $functions = parent::getFunctions();
        $functions['canSubscribeToBoard'] = new \Twig_Function_Method($this, 'canSubscribeToBoard');
        $functions['canUnsubscribeFromBoard'] = new \Twig_Function_Method($this, 'canUnsubscribeFromBoard');
        
        return $functions;
    }
    
    public function canSubscribeToBoard(Board $board, Forum $forum = null, Subscription $subscription = null)
    {
        return $this->authorizer->canSubscribeToBoard($board, $forum, $subscription);
    }
    
    public function canUnsubscribeFromBoard(Board $board, Forum $forum = null, Subscription $subscription = null)
    {
        return $this->authorizer->canUnsubscribeFromBoard($board, $forum, $subscription);
    }
}
