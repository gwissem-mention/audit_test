<?php

namespace HopitalNumerique\ForumBundle\Component\Security;

use Symfony\Component\Security\Core\SecurityContextInterface;

use CCDNForum\ForumBundle\Component\Helper\PostLockHelper;
use CCDNForum\ForumBundle\Entity\Forum;
use CCDNForum\ForumBundle\Entity\Category;
use CCDNForum\ForumBundle\Entity\Board;
use CCDNForum\ForumBundle\Entity\Topic;
use CCDNForum\ForumBundle\Entity\Post;
use CCDNForum\ForumBundle\Entity\Subscription;

use CCDNForum\ForumBundle\Component\Security\Authorizer as CCDNAuthorizer;

class Authorizer extends CCDNAuthorizer
{
    
    public function canDeleteTopic(Topic $topic, Forum $forum = null)
    {
        if ($topic->isDeleted()) {
            return false;
        }

        if (! $this->canShowTopic($topic, $forum) && ! $this->securityContext->isGranted('ROLE_ADMIN')) {
            return false;
        }

        return true;
    }



    public function canEditPost(Post $post, Forum $forum = null)
    {
        if (! $this->securityContext->isGranted('ROLE_USER')) {
            return false;
        }

        if (! $this->canShowPost($post, $forum) && ! $this->securityContext->isGranted('ROLE_ADMIN')) {
            return false;
        }

        if (! $this->securityContext->isGranted('ROLE_ADMIN')) {
            if (! $post->getCreatedBy()) {
                return false;
            } else {
                if ($post->getCreatedBy()->getId() != $this->securityContext->getToken()->getUser()->getId()) {
                    return false;
                }
            }
        }

        return true;
    }

    public function canDeletePost(Post $post, Forum $forum = null)
    {
        if ($post->isDeleted()) {
            return false;
        }

        if (! $this->securityContext->isGranted('ROLE_USER')) {
            return false;
        }

        if (! $this->canShowPost($post, $forum) && ! $this->securityContext->isGranted('ROLE_MODERATOR')) {
            return false;
        }

        if (! $this->securityContext->isGranted('ROLE_ADMIN')) {
            if (! $post->getCreatedBy()) {
                return false;
            } else {
                if ($post->getCreatedBy()->getId() != $this->securityContext->getToken()->getUser()->getId()) {
                    return false;
                }
            }
        }

        return true;
    }

    
    public function canSubscribeToBoard(Board $board, Forum $forum = null, Subscription $subscription = null)
    {
        if (! $this->securityContext->isGranted('ROLE_USER')) {
            return false;
        }
    
        if (! $this->canShowBoard($board, $forum)) {
            return false;
        }
    
        if ($subscription) {
            if ($subscription->getBoard()) {
                if ($subscription->getBoard()->getId() != $board->getId()) {
                    return false;
                }
            }
    
            if ($subscription->isSubscribed()) {
                return false;
            }
        }
    
        return true;
    }
    
    public function canUnsubscribeFromBoard(Board $board, Forum $forum = null, Subscription $subscription = null)
    {
        if (! $this->securityContext->isGranted('ROLE_USER')) {
            return false;
        }
    
        if (! $this->canShowBoard($board, $forum)) {
            return false;
        }
    
        if ($subscription) {
            if ($subscription->getBoard()) {
                if ($subscription->getBoard()->getId() != $board->getId()) {
                    return false;
                }
            }
    
            if (! $subscription->isSubscribed()) {
                return false;
            }
        } else {
            return false;
        }
    
        return true;
    }
}
