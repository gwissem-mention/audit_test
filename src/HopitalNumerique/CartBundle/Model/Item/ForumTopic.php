<?php

namespace HopitalNumerique\CartBundle\Model\Item;

use HopitalNumerique\ForumBundle\Entity\Topic;

class ForumTopic extends Item
{
    /**
     * @var Topic $topic
     */
    protected $topic;

    /**
     * ForumTopic constructor.
     *
     * @param Topic $topic
     */
    public function __construct(Topic $topic)
    {
        $this->topic = $topic;
    }

    /**
     * @return Topic
     */
    public function getObject()
    {
        return $this->topic;
    }

    public function getTitle()
    {
        return $this->topic->getTitle();
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return \HopitalNumerique\CartBundle\Entity\Item::FORUM_TOPIC_TYPE;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->topic->getId();
    }

    /**
     * @inheritdoc
     */
    public function getRoute()
    {
        return 'ccdn_forum_user_topic_show';
    }

    /**
     * @inheritdoc
     */
    public function getRouteParameters()
    {
        return [
            'topicId' => $this->topic->getId(),
            'forumName' => $this->topic->getBoard()->getCategory()->getForum()->getName(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getDomains()
    {
        return $this->topic->getBoard()->getCategory()->getDomaines();
    }
}
