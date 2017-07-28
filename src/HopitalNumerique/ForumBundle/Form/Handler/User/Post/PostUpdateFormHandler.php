<?php

namespace HopitalNumerique\ForumBundle\Form\Handler\User\Post;

use CCDNForum\ForumBundle\Entity\Post;
use CCDNForum\ForumBundle\Component\Dispatcher\ForumEvents;
use CCDNForum\ForumBundle\Component\Dispatcher\Event\UserPostEvent;
use CCDNForum\ForumBundle\Form\Handler\User\Post\PostUpdateFormHandler as BaseHandler;

/**
 * {@inheritdoc}
 */
class PostUpdateFormHandler extends BaseHandler
{
    /**
     * {@inheritdoc}
     */
    protected function onSuccess(Post $post)
    {
        $post->setEditedDate(new \DateTime());
        $post->setEditedBy($this->user);

        $this->dispatcher->dispatch(ForumEvents::USER_POST_EDIT_SUCCESS, new UserPostEvent($this->request, $this->post));

        $this->postModel->updatePost($post);

        $this->dispatcher->dispatch(ForumEvents::USER_POST_EDIT_COMPLETE, new UserPostEvent($this->request, $post));
    }
}
