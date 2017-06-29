<?php

namespace HopitalNumerique\CartBundle\Model\Report;

use HopitalNumerique\ForumBundle\Entity\Post;
use HopitalNumerique\ForumBundle\Entity\Topic;

class ForumTopic implements ItemInterface
{
    /**
     * @var Topic $topic
     */
    protected $topic;

    /**
     * @var array $references
     */
    protected $references;

    /**
     * ForumTopic constructor.
     *
     * @param Topic $topic
     * @param array $references
     */
    public function __construct(Topic $topic, $references)
    {
        $this->topic = $topic;
        $this->references = $references;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->topic->getTitle();
    }

    /**
     * @return \DateTime
     */
    public function getFirstPostPublicationDate()
    {
        return $this->topic->getFirstPost()->getCreatedDate();
    }

    /**
     * @return \DateTime
     */
    public function getLastPostPublicationDate()
    {
        return $this->topic->getLastPost()->getCreatedDate();
    }

    /**
     * @return Post
     */
    public function getFirstPost()
    {
        return $this->topic->getFirstPost();
    }

    /**
     * @return Post[]
     */
    public function getPosts()
    {
        return $this->topic->getPosts()->filter(function (Post $post) {
            return $post !== $this->topic->getFirstPost();
        });
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'forumTopic';
    }

    /**
     * @return array
     */
    public function getReferences()
    {
        return $this->references;
    }
}
