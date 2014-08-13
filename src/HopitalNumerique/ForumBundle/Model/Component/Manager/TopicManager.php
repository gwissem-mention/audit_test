<?php

namespace HopitalNumerique\ForumBundle\Model\Component\Manager;

use Symfony\Component\Security\Core\User\UserInterface;

use CCDNForum\ForumBundle\Entity\Topic;
use CCDNForum\ForumBundle\Model\Component\Manager\TopicManager as CCDNTopicManager;

class TopicManager extends CCDNTopicManager
{
    public function softDelete(Topic $topic, UserInterface $user)
    {
        $this->_em->remove($topic);
        
        // update the record before doing record counts
        $this->persist($topic)->flush();

        return $this;
    }
}