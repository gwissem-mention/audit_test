<?php
namespace HopitalNumerique\ForumBundle\Model\FrontModel;

use CCDNForum\ForumBundle\Model\FrontModel\SubscriptionModel as SubscriptionModelBase;
use HopitalNumerique\ForumBundle\Entity\Board;
use Symfony\Component\Security\Core\User\UserInterface;
use HopitalNumerique\ForumBundle\Entity\Topic;

class SubscriptionModel extends SubscriptionModelBase
{
    /**
     *
     * @access public
     * @param  \HopitalNumerique\ForumBundle\Entity\Board          $board
     * @param  \Symfony\Component\Security\Core\User\UserInterface $userId
     * @return \CCDNForum\ForumBundle\Entity\Subscription
     */
    public function findOneSubscriptionForBoardAndUser(Board $board, UserInterface $user)
    {
        return $this->getRepository()->findOneSubscriptionForBoardAndUser($board, $user);
    }
    
    /**
     *
     * @access public
     * @param  int                                                             $topicId
     * @param  \Symfony\Component\Security\Core\User\UserInterface             $userId
     * @return \CCDNForum\ForumBundle\Model\Component\Manager\ManagerInterface
     */
    public function subscribeBoard(Board $board, UserInterface $user)
    {
        return $this->getManager()->subscribeBoard($board, $user);
    }
    
    /**
     *
     * @access public
     * @param  int                                                             $topicId
     * @param  int                                                             $userId
     * @return \CCDNForum\ForumBundle\Model\Component\Manager\ManagerInterface
     */
    public function unsubscribeBoard(Board $board, UserInterface $user)
    {
        return $this->getManager()->unsubscribeBoard($board, $user);
    }

    /**
     * Retourne les Subscription à envoyer dès que l'on répond à un topic.
     * 
     * @param \HopitalNumerique\ForumBundle\Entity\Topic $topic
     */
    public function findAllSubscriptionsToSend(Topic $topic)
    {
        return $this->getRepository()->findAllSubscriptionsToSend($topic);
    }
}
