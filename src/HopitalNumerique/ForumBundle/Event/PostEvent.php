<?php

namespace HopitalNumerique\ForumBundle\Event;

use CCDNForum\ForumBundle\Entity\Post;
use Symfony\Component\EventDispatcher\Event;

class PostEvent extends Event
{
    /**
     * @var Post
     */
    protected $post;

    /**
     * PostEvent constructor.
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * @return Post
     */
    public function getPost()
    {
        return $this->post;
    }
}
