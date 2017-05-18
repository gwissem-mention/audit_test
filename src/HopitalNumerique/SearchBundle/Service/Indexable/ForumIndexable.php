<?php

namespace HopitalNumerique\SearchBundle\Service\Indexable;

use HopitalNumerique\ForumBundle\Entity\Post;
use HopitalNumerique\ForumBundle\Entity\Topic;

/**
 * Forum types indexable.
 * This class is responsible of saying if a Forum post or topic is indexable
 */
class ForumIndexable
{
    /**
     * @var string
     */
    protected $domaineSlug;

    /**
     * PublicationIndexable constructor.
     *
     * @param string $domaineSlug
     */
    public function __construct($domaineSlug)
    {
        $this->domaineSlug = $domaineSlug;
    }

    /**
     * Check if $post is indexable
     *
     * @param Post $post
     *
     * @return bool
     */
    public function isPostIndexable(Post $post)
    {
        return null !== $post->getTopic()
            && null !== $post->getTopic()->getBoard()
            && null !== $post->getTopic()->getBoard()->getCategory()
            && null !== $post->getTopic()->getBoard()->getCategory()->getForum()
        ;
    }

    /**
     * Check if $topic is indexable
     *
     * @param Topic $topic
     *
     * @return bool
     */
    public function isTopicIndexable(Topic $topic)
    {
        return null !== $topic->getBoard()
            && null !== $topic->getBoard()->getCategory()
            && null !== $topic->getBoard()->getCategory()->getForum()
            ;
    }
}
