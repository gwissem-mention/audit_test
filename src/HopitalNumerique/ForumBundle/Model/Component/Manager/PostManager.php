<?php

namespace HopitalNumerique\ForumBundle\Model\Component\Manager;

use Symfony\Component\Security\Core\User\UserInterface;

use CCDNForum\ForumBundle\Model\Component\Gateway\GatewayInterface;
use CCDNForum\ForumBundle\Component\Helper\PostLockHelper;
use CCDNForum\ForumBundle\Entity\Post;
use CCDNForum\ForumBundle\Model\Component\Manager\PostManager as CCDNPostManager;

use HopitalNumerique\ForumBundle\Manager\PostManager as ClassicPostManager;
use HopitalNumerique\ForumBundle\Manager\TopicManager as ClassicTopicManager;

class PostManager extends CCDNPostManager
{
    protected $_postManager;
    protected $_topicManager;

    /**
     *
     * @access public
     * @param \CCDNForum\ForumBundle\Gateway\GatewayInterface        $gateway
     * @param \CCDNForum\ForumBundle\Component\Helper\PostLockHelper $postLockHelper
     */
    public function __construct(GatewayInterface $gateway, PostLockHelper $postLockHelper, ClassicPostManager $postManager, ClassicTopicManager $topicManager)
    {
        parent::__construct($gateway, $postLockHelper);

        //Récupération des managers custom pour traiter correctement les entités
        $this->_postManager  = $postManager;
        $this->_topicManager = $topicManager;
    }

    /**
     *
     * @access public
     * @param  \CCDNForum\ForumBundle\Entity\Post                  $post
     * @param  \Symfony\Component\Security\Core\User\UserInterface $user
     * @return \CCDNForum\ForumBundle\Manager\ManagerInterface
     */
    public function softDelete(Post $post, UserInterface $user)
    {
        $topic = $post->getTopic();

        $this->_postManager->delete($post);
        
        if(count($topic->getPosts()) == 0)
            $this->_topicManager->delete($topic);

        return $this;
    }
}
