<?php

namespace HopitalNumerique\ForumBundle\Form\Handler\User\Post;

use CCDNForum\ForumBundle\Component\Dispatcher\ForumEvents;
use CCDNForum\ForumBundle\Component\Dispatcher\Event\UserPostEvent;
use CCDNForum\ForumBundle\Component\Dispatcher\Event\UserTopicEvent;
use CCDNForum\ForumBundle\Entity\Post;

use CCDNForum\ForumBundle\Form\Handler\User\Post\PostCreateFormHandler as BaseHandler;

/**
 *Surcharge pour ajouter des events
 *
 */
class PostCreateFormHandler extends BaseHandler
{
    /**
     *
     * 
     * @access protected
     * @param \HopitalNumerique\ForumBundle\Entity\Post $post
     */
    protected function onSuccess(Post $post)
    {
        $post->setCreatedDate(new \DateTime());
        $post->setCreatedBy($this->user);
        $post->setTopic($this->topic);
        $post->setDeleted(false);

        $this->dispatcher->dispatch(ForumEvents::USER_TOPIC_REPLY_SUCCESS, new UserTopicEvent($this->request, $post->getTopic()));

        $this->postModel->savePost($post);
        $this->dispatcher->dispatch('hopitalnumerique.user.post.create.success', new UserPostEvent($this->request, $post));

        $this->dispatcher->dispatch(ForumEvents::USER_TOPIC_REPLY_COMPLETE, new UserTopicEvent($this->request, $this->topic, $this->didAuthorSubscribe()));
    }
}
