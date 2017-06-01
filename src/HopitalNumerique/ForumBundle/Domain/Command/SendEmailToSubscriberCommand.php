<?php

namespace HopitalNumerique\ForumBundle\Domain\Command;

use HopitalNumerique\ForumBundle\Entity\Post;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Class SendEmailToSubscriberCommand
 */
class SendEmailToSubscriberCommand
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var Post
     */
    public $post;

    /**
     * SendEmailToSubscriberCommand constructor.
     *
     * @param User $user
     * @param Post $post
     */
    public function __construct(User $user, Post $post)
    {
        $this->user = $user;
        $this->post = $post;
    }
}
